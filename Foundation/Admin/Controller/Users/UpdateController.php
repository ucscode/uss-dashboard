<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractFieldConstructor;
use Module\Dashboard\Foundation\Admin\Controller\Users\Process\OnUpdateSubmit;
use Ucscode\UssForm\Gadget\Gadget;

class UpdateController extends AbstractFieldConstructor
{
    protected function composeMicroApplication(): void
    {
        parent::composeMicroApplication();
        $this->enableDocumentMenu('main:users');

        $this->crudEditor->getForm()->addSubmitAction(
            'user:update', 
            new OnUpdateSubmit($this->client, $this->crudEditor)
        );
        
        $this->crudEditor->getForm()->handleSubmission()
        ->then(function() {
            $form = $this->crudEditor->getForm();
            $persisted = $form->getProperty('entity.isPersisted');
            if($persisted) {
                $this->initializeClient();
                $this->updateSecondaryCollections();
            }
        });
    }

    protected function updateSecondaryCollections(): void
    {
        $this->iterateRolesGadget(function(Gadget $gadget) {
            $role = $gadget->widget->getAttribute('data-role');
            $gadget->widget->setChecked(
                $this->client->roles->has($role)
            );
        }, true);
    }
}