<?php

use Ucscode\SQuery\SQuery;

class CrudEditFormSubmissionHandler implements CrudActionImmutableInterface
{
    protected const MODE_WARNING = 'warning';
    protected const MODE_SUCCESS = 'success';
    protected const MODE_ERROR = 'error';
    protected const MODE_INFO = 'info';
    protected const DO_ALERT = 'alert';
    protected const DO_REDIRECT = 'redirect';

    protected string $userAction;
    protected array $item;
    protected bool $defaultReaction = true;
    protected ?CrudEditSubmitInterface $submitInterface;
    protected array $persistion = [
        'action' => null,
        'mode' => null,
        'value' => null,
    ];

    public function __construct(
        protected CrudEditManager $crudEditManager
    ) {
        $this->submitInterface = $this->crudEditManager->getModifier();
        $this->submissionEntry();
    }

    /**
     * @method submissionEntry
     */
    protected function submissionEntry(): void
    {
        if(!empty($_POST) && !empty($_POST['__NONCE__'])) {
            if($this->isValidNonce()) {
                $this->item = $_POST;
                $this->overwriteItem();
            } else {
                (new Alert('Security check not approved'))
                    ->type('notification')
                    ->display('warning');
            }
        }
    }

    /**
     * @method processFormSubmission
     */
    protected function overwriteItem(): void
    {
        if($this->submitInterface) {
            $this->item = $this->submitInterface->beforeEntry($this->item);
        };

        if($this->submitInterface instanceof CrudEditSubmitCustomInterface) {
            $status = $this->submitInterface->onSubmit($this->item);
        } else {
            $status = $this->processitem();
        }

        if($this->submitInterface) {
            $this->defaultReaction = $this->submitInterface->afterEntry($status, $this->item);
        }

        if($this->defaultReaction) {
            $this->useSystemReaction();
        }
    }

    /**
     * @method validateNonce
     */
    protected function isValidNonce(): bool
    {
        $this->userAction = $_POST['__ACTION__'] ?? self::ACTION_CREATE;
        $result = Uss::instance()->nonce($this->crudEditManager->tablename, $_POST['__NONCE__']);
        unset($_POST['__NONCE__']);
        unset($_POST['__ACTION__']);
        return $result;
    }

    /**
     * @method processitem
     */
    protected function processitem(): bool
    {
        try {

            if($this->userAction === self::ACTION_CREATE) {

                $status = $this->validateitem(function ($sQuery, $uss) {

                    $sQuery->insert($this->crudEditManager->tablename, $this->item);
                    $status = $uss->mysqli->query($sQuery);

                    if($status) {
                        $this->persistion['action'] = self::DO_REDIRECT;
                        $this->persistion['value'] = $this->href();
                    } else {
                        $this->persistion['action'] = self::DO_ALERT;
                        $this->persistion['mode'] = self::MODE_WARNING;
                        $this->persistion['value'] = 'The item could not be created';
                    }

                    return $status;

                });

            } else {

                if(in_array($this->userAction, [self::ACTION_CREATE, self::ACTION_UPDATE], true)) {

                    $status = $this->validateitem(function ($sQuery, $uss) {

                        $primaryKey = $this->crudEditManager->getPrimaryKey();
                        $primaryValue = $this->crudEditManager->getItem($primaryKey);

                        if(is_null($primaryValue)) {
                            throw new \Exception(
                                sprintf(
                                    'Cannot %s item without matching value for primary key "%s"',
                                    $this->userAction,
                                    $primaryKey
                                )
                            );
                        };

                        if($this->userAction === self::ACTION_UPDATE) {

                            $sQuery->update($this->crudEditManager->tablename, $this->item)
                                ->where($primaryKey, $primaryKey);

                            $status = $uss->mysqli->query($sQuery);

                            $this->persistion['action'] = self::DO_ALERT;

                            if($status) {
                                $this->persistion['mode'] = self::MODE_SUCCESS;
                                $this->persistion['value'] = 'The item was successfully updated';
                            } else {
                                $this->persistion['mode'] = self::MODE_WARNING;
                                $this->persistion['value'] = 'The item could not be updated';
                            }

                        } elseif($this->userAction === self::ACTION_DELETE) {

                            $sQuery->delete($this->crudEditManager->tablename)
                                ->where($primaryKey, $primaryValue);

                            $status = $uss->mysqli->query($sQuery);

                            if($status) {
                                $this->persistion['action'] = self::DO_REDIRECT;
                                $this->persistion['value'] = $this->href();
                            } else {
                                $this->persistion['action'] = self::DO_ALERT;
                                $this->persistion['mode'] = self::MODE_WARNING;
                                $this->persistion['value'] = 'The item could not be deleted';
                            }

                        }

                        return $status;

                    });

                } else {

                    if(!$this->submitInterface || !($this->submitInterface instanceof CrudEditSubmitCustomInterface)) {
                        throw new \Exception(
                            sprintf(
                                "Cannot find a way to handle custom action named '%s'; %s() method requires an argument that implements %s interface",
                                $this->userAction,
                                'createUI',
                                CrudEditSubmitCustomInterface::class
                            )
                        );
                    }

                    $status = $this->submitInterface->onSubmit($this->item);

                    $this->persistion['action'] = self::DO_ALERT;

                    if($status) {
                        $this->persistion['value'] = 'The process was successful';
                        $this->persistion['mode'] = self::MODE_INFO;
                    } else {
                        $this->persistion['value'] = 'The process was unsuccessful';
                        $this->persistion['mode'] = self::MODE_WARNING;
                    };

                }

            }

        } catch(\Exception $e) {

            $status = false;

            $this->persistion['action'] = self::DO_ALERT;
            $this->persistion['mode'] = self::MODE_ERROR;
            $this->persistion['value'] = 'Request Failed: A critical error occured';

        }

        return $status;

    }    /**
     * @method takePersisionAction
     */
    protected function useSystemReaction(): void
    {
        if($this->persistion['action'] === self::DO_ALERT) {
            (new Alert())
                ->setOption('message', $this->persistion['value'])
                ->type('notification')
                ->display($this->persistion['mode']);
        } else {
            header('location: ' . $this->persistion['value']);
            die;
        }
    }

    /**
     * @method validateitem
     */
    protected function validateitem(closure $caller): bool
    {
        $status = true;

        foreach($this->item as $key => $value) {
            $crudField = $this->crudEditManager->getField($key);

            if($crudField) {

                switch($crudField->getType()) {

                    case CrudField::TYPE_NUMBER:
                        if(!is_numeric($value)) {
                            $status = false;
                            $crudField->setError('Invalid numeric value');
                        }
                        break;

                    case CrudField::TYPE_DATE:
                        try {
                            $valid = new DateTime($value);
                        } catch(\Exception $e) {
                            $status = false;
                            $crudField->setError('Invalid date/time format');
                        }
                        break;

                    case CrudField::TYPE_EMAIL:
                        if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $status = false;
                            $crudField->setError('Invalid email address');
                        }
                        break;
                }
            }
        }

        if($status) {
            $status = $caller(new SQuery(), Uss::instance());
        } else {
            $this->persistion['action'] = self::DO_ALERT;
            $this->persistion['mode'] = self::MODE_WARNING;
            $this->persistion['value'] = 'One or more fields contain invalid input';
        };

        return $status;

    }

    /**
     * @method href
     */
    protected function href(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
}