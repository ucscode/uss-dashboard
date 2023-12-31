<?php

use Ucscode\SQuery\SQuery;
use Ucscode\Form\Form;
use Ucscode\Form\FormField;

final class CrudEditFormSubmissionHandler implements CrudActionImmutableInterface
{
    protected const MODE_WARNING = 'warning';
    protected const MODE_SUCCESS = 'success';
    protected const MODE_ERROR = 'error';
    protected const MODE_INFO = 'info';

    protected const EXPRESS_ALERT = 'alert';
    protected const EXPRESS_REDIRECT = 'redirect';

    protected ?CrudEditSubmitInterface $submitInterface;

    protected string $userAction;
    protected array $item;
    protected bool $defaultReaction = true;

    protected array $persistion = [
        'express' => null,
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
     * @method processitem
     */
    protected function processitem(): bool
    {
        try {
            if($this->userAction === self::ACTION_CREATE) {
                $status = $this->executeCreateAction();
            } else {
                $isHybrid = in_array($this->userAction, [self::ACTION_DELETE, self::ACTION_UPDATE]);
                if($isHybrid) {
                    $status = $this->executeHybridAction();
                } else {
                    $status = $this->executeCustomAction();
                }
            }
        } catch(\Exception $e) {
            $status = false;
            $this->persistion['express'] = self::EXPRESS_ALERT;
            $this->persistion['mode'] = self::MODE_ERROR;
            $this->persistion['value'] = 'Request Failed: A critical error occured';
            error_log($e->getMessage());
        }
        return $status;
    }

    /**
     * @method createAction
     */
    public function executeCreateAction(): bool
    {
        return $this->validateItemProperties(function ($sQuery, $uss) {

            $sQuery->insert($this->crudEditManager->tablename, $this->item);
            $status = $uss->mysqli->query($sQuery);

            if($status) {
                $this->persistion['express'] = self::EXPRESS_REDIRECT;
                $this->persistion['value'] = $this->href();

                $this->item = $uss->fetchItem(
                    $this->crudEditManager->tablename,
                    $uss->mysqli->insert_id
                );
            } else {
                $this->persistion['express'] = self::EXPRESS_ALERT;
                $this->persistion['mode'] = self::MODE_WARNING;
                $this->persistion['value'] = 'The item could not be created';
            }

            return $status;
        });
    }

    /**
     * @method executeEditor
     */
    public function executeHybridAction(): bool
    {
        return $this->validateItemProperties(function ($sQuery, $uss) {

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

            if($this->userAction === self::ACTION_DELETE) {
                return $this->executeDeleteAction($sQuery, $uss, $primaryKey, $primaryValue);
            } else {
                return $this->executeUpdateAction($sQuery, $uss, $primaryKey, $primaryValue);
            }
        });
    }

    /**
     * @method executeUpdateAction
     */
    protected function executeUpdateAction(SQuery $sQuery, Uss $uss, string $primaryKey, string $primaryValue): bool
    {
        $sQuery
            ->update($this->crudEditManager->tablename, $this->item)
            ->where($primaryKey, $primaryValue);

        $status = $uss->mysqli->query($sQuery);

        $this->persistion['express'] = self::EXPRESS_ALERT;

        if($status) {
            $this->persistion['mode'] = self::MODE_SUCCESS;
            $this->persistion['value'] = 'The item was successfully updated';

            $this->item = $uss->fetchItem(
                $this->crudEditManager->tablename,
                $primaryValue,
                $primaryKey
            );
        } else {
            $this->persistion['mode'] = self::MODE_WARNING;
            $this->persistion['value'] = 'The item could not be updated';
        }

        return $status;
    }

    /**
     * @method executeDeleteAction
     */
    public function executeDeleteAction(SQuery $sQuery, Uss $uss, string $primaryKey, string $primaryValue): bool
    {
        $sQuery
            ->delete($this->crudEditManager->tablename)
            ->where($primaryKey, $primaryValue);

        $status = $uss->mysqli->query($sQuery);

        if($status) {
            $this->persistion['express'] = self::EXPRESS_REDIRECT;
            $this->persistion['value'] = $this->href();
        } else {
            $this->persistion['express'] = self::EXPRESS_ALERT;
            $this->persistion['mode'] = self::MODE_WARNING;
            $this->persistion['value'] = 'The item could not be deleted';
        }

        return $status;
    }

    /**
     * @method executeCustomAction
     */
    protected function executeCustomAction(): bool
    {
        if(!$this->submitInterface || !($this->submitInterface instanceof CrudEditSubmitCustomInterface)) {
            throw new \Exception(
                sprintf(
                    "Cannot find a way to handle custom action named '%s'; Please call %s() method with an argument that implements %s interface",
                    $this->userAction,
                    'setModifier',
                    CrudEditSubmitCustomInterface::class
                )
            );
        }

        $status = $this->submitInterface->onSubmit($this->item);

        $this->persistion['express'] = self::EXPRESS_ALERT;

        if($status) {
            $this->persistion['value'] = 'The process was successful';
            $this->persistion['mode'] = self::MODE_INFO;
        } else {
            $this->persistion['value'] = 'The process was unsuccessful';
            $this->persistion['mode'] = self::MODE_WARNING;
        };

        return $status;
    }

    /**
     * @method validateitem
     */
    protected function validateItemProperties(closure $caller): bool
    {
        $isValid = true;
        foreach($this->item as $key => $value) {
            if($isValid) {
                $field = $this->crudEditManager->getField($key);
                if($field) {
                    if($field->isRequired() && trim($value) === '') {
                        $isValid = false;
                    } elseif(!empty($value) && $field->nodeName === Form::NODE_INPUT) {
                        $isValid = $this->validityPriority($field->getWidgetAttribute('type'), $value, $field);
                    }
                }
            }
        }

        if($isValid) {
            return !!$caller(new SQuery(), Uss::instance());
        } else {
            $this->persistion['express'] = self::EXPRESS_ALERT;
            $this->persistion['mode'] = self::MODE_WARNING;
            $this->persistion['value'] = 'One or more fields contain invalid input';
        };

        return $isValid;
    }

    /**
     * @method validityPriority
     */
    protected function validityPriority(string $type, string $value, FormField $field): bool
    {
        switch($type) {
            case Form::TYPE_NUMBER:
                if(!is_numeric($value)) {
                    return !$field->setValidationMessage('Invalid numeric value');
                }
                break;

            case Form::TYPE_DATE:
                try {
                    new DateTime($value);
                } catch(\Exception $e) {
                    return !$field->setValidationMessage('Invalid date/time format');
                }
                break;

            case Form::TYPE_EMAIL:
                if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return !$field->setValidationMessage('Invalid email address');
                }
                break;

            default:
                $pattern = trim($field->getWidgetAttribute('pattern'));
                if(!empty($pattern)) {
                    if(!preg_match("#{$pattern}#", $value)) {
                        return !$field->setValidationMessage(
                            'Invalid ' . $field->getLabelValue() . ' pattern'
                        );
                    }
                };
        }
        return true;
    }

    /**
     * @method takePersisionAction
     */
    protected function useSystemReaction(): void
    {
        if($this->persistion['express'] === self::EXPRESS_ALERT) {
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
     * @method href
     */
    protected function href(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
}
