<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractFieldConstructor;
use Module\Dashboard\Foundation\Admin\Controller\Users\Process\OnUpdateSubmit;
use Ucscode\UssForm\Gadget\Gadget;

class UpdateController extends AbstractFieldConstructor
{
    protected function composeMicroApplication(): void
    {
        parent::composeMicroApplication();
        $this->enableDocumentMenu('main:users');

        $submitAction = new OnUpdateSubmit($this->client, $this->crudEditor);

        $this->crudEditor->getForm()
            ->addSubmitAction('user:update', $submitAction)
            ->handleSubmission()
            ->then(function(CrudEditorForm $form) {
                $persisted = $form->getProperty('entity.isPersisted');
                if($persisted) {
                    $this->initializeClient();
                    $this->updateSecondaryCollections();
                    return;
                }
            });
    }

    protected function updateSecondaryCollections(?array $roles = null): void
    {
        $this->iterateRolesGadget(function(Gadget $gadget) use ($roles) {
            $role = $gadget->widget->getAttribute('data-role');
            $checked = !empty($roles) ? in_array($role, $roles) : $this->client->roles->has($role);
            $gadget->widget->setChecked($checked);
        }, true);
    }
}