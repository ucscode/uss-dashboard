<?php

use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;

class CrudEditManager extends AbstractCrudEditManagerLogics
{
    protected const DO_REDIRECT = 'redirect';
    protected const DO_ALERT = 'alert';
    protected const MODE_WARNING = 'warning';
    protected const MODE_SUCCESS = 'success';
    protected const MODE_ERROR = 'error';
    protected const MODE_INFO = 'info';

    protected CrudEditSubmitInterface|CrudEditSubmitCustomInterface|null $submitInterface;
    protected string $submitAction;

    protected array $persistion = [
        'action' => null,
        'mode' => null,
        'value' => null,
    ];

    protected array $dataToSubmit;
    protected bool $takeDefaultAction = false;

    /**
     * @method createUI
     */
    public function createUI(CrudEditSubmitInterface|CrudEditSubmitCustomInterface|null $submitInterface): UssElement
    {
        $this->submitInterface = $submitInterface;
        $this->processFormSubmission();

        $container = new UssElement(UssElement::NODE_DIV);
        $container->setAttribute('class', 'crud-edit-container');

        $this->editForm = new UssForm(
            $this->tablename . '-crud-edit',
            $this->getSubmitUrl(),
            'POST',
            'multipart/form-data'
        );

        $position = $this->getAlignActionsLeft() ? 'start' : 'end';

        $this->actionContainer = $this->editForm->addRow('action-container')
            ->removeAttributeValue('class', 'row')
            ->addAttributeValue(
                'class',
                sprintf('border-bottom py-2 mb-2 text-%s', $position)
            );

        $this->widgetContainer = $this->editForm->addRow('widget-container');

        $this->editForm->addRow();

        foreach($this->fields as $key => $crudField) {
            if($crudField->getType() !== CrudField::TYPE_EDITOR) {
                $this->editForm->add(
                    $key,
                    $this->getNodeName($crudField),
                    $this->getFieldContext($crudField),
                    $this->getFieldConfig($crudField)
                );
                if($crudField->hasLineBreak()) {
                    $this->editForm->addRow();
                }
            } else {
                $this->createCustomField($this->editForm);
            }
        }

        $this->populateForm();
        $this->addFormNonce();

        $container->appendChild($this->editForm);

        $this->insertActions();
        $this->insertWidgets();

        return $container;
    }

    /**
     * @method processFormSubmission
     */
    protected function processFormSubmission(): void
    {
        if(!empty($_POST) && !empty($_POST['__NONCE__'])) {
            if($this->validateNonce()) {
                $this->dataToSubmit = $_POST;

                if($this->submitInterface) {
                    $this->dataToSubmit = $this->submitInterface->beforeEntry($_POST);
                };

                if($this->submitInterface && property_exists($this->submitInterface, 'onSubmit')) {
                    $status = $this->submitInterface->onSubmit($this->dataToSubmit);
                } else {
                    $status = $this->processDataToSubmit();
                }

                if($this->submitInterface) {
                    $this->takeDefaultAction = $this->submitInterface->afterEntry($status, $this->dataToSubmit);
                }

                if($this->takeDefaultAction) {
                    $this->takePersistionAction();
                }

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
    protected function validateNonce(): bool
    {
        $uss = Uss::instance();
        $nonce = $_POST['__NONCE__'];
        unset($_POST['__NONCE__']);
        $this->submitAction = $_POST['__action'] ?? self::ACTION_CREATE;
        unset($_POST['__ACTION__']);
        return $uss->nonce($this->nonceKey, $nonce);
    }

    /**
     * @method processDataToSubmit
     */
    protected function processDataToSubmit(): bool
    {
        try {

            if($this->submitAction === self::ACTION_CREATE) {

                $status = $this->validateDataToSubmit(function ($sQuery, $uss) {

                    $sQuery->insert($this->tablename, $this->dataToSubmit);
                    $status = $uss->mysqli->query($sQuery);

                    if($status) {
                        $this->persistion['action'] = self::DO_REDIRECT;
                        $this->persistion['value'] = $this->baseUrl;
                    } else {
                        $this->persistion['action'] = self::DO_ALERT;
                        $this->persistion['mode'] = self::MODE_WARNING;
                        $this->persistion['value'] = 'The item could not be created';
                    }

                    return $status;

                });

            } else {

                if(in_array($this->submitAction, [self::ACTION_CREATE, self::ACTION_UPDATE], true)) {

                    $status = $this->validateDataToSubmit(function ($sQuery, $uss) {

                        $primaryKey = $this->getPrimaryKey();
                        $primaryValue = $this->getItem($primaryKey);

                        if(is_null($primaryValue)) {
                            throw new \Exception(
                                sprintf(
                                    'Cannot %s item without matching value for primary key "%s"',
                                    $this->submitAction,
                                    $primaryKey
                                )
                            );
                        };

                        if($this->submitAction === self::ACTION_UPDATE) {

                            $sQuery->update($this->tablename, $this->dataToSubmit)
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

                        } elseif($this->submitAction === self::ACTION_DELETE) {

                            $sQuery->delete($this->tablename)
                                ->where($primaryKey, $primaryValue);

                            $status = $uss->mysqli->query($sQuery);

                            if($status) {
                                $this->persistion['action'] = self::DO_REDIRECT;
                                $this->persistion['value'] = $this->baseUrl;
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
                                $this->submitAction,
                                'createUI',
                                CrudEditSubmitCustomInterface::class
                            )
                        );
                    }

                    $status = $this->submitInterface->onSubmit($this->dataToSubmit);

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

    }

    /**
     * @method takePersisionAction
     */
    protected function takePersistionAction(): void
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
     * @method validateDataToSubmit
     */
    protected function validateDataToSubmit(closure $caller): bool
    {
        $status = true;

        foreach($this->dataToSubmit as $key => $value) {
            $crudField = $this->getField($key);

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
     * @method populateForm
     */
    protected function populateForm(): void
    {
        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        $populate = $isPost || $this->getItem();
        $discardKey = ['__ACTION__', '__NONCE__'];
        $dataToPopulate = $_POST;

        foreach($discardKey as $key) {
            if(isset($dataToPopulate[$key])) {
                unset($dataToPopulate[$key]);
            }
        }

        if($populate) {
            if($isPost) {
                $this->editForm->populate($dataToPopulate);
            } else {
                $this->editForm->populate($this->getItem());
            }
            $this->editForm->populate(true);
        }
    }
}
