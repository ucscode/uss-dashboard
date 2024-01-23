<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractFieldConstructor;
use Module\Dashboard\Foundation\Admin\Controller\Users\Process\OnUpdateSubmit;
use Module\Dashboard\Foundation\Admin\Controller\Users\Tool\UserControl;
use Ucscode\UssForm\Gadget\Gadget;

class UpdateController extends AbstractFieldConstructor
{
    protected function composeMicroApplication(): void
    {
        parent::composeMicroApplication();
        $this->enableDocumentMenu('main:users');

        $submitAction = new OnUpdateSubmit($this->client, $this->crudEditor);

        $promise = $this->crudEditor->getForm()
            ->addSubmitAction('user:update', $submitAction)
            ->handleSubmission();
        
        $promise->then(function(CrudEditorForm $form) {
            $persisted = $form->getProperty('entity.isPersisted');
            if($persisted) {
                $this->initializeClient();
                $roles = $this->client->roles->getAll();
                (new UserControl($this->crudEditor))->autoCheckRolesCheckbox($roles);
                return;
            }
        });
    }
}