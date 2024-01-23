<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Service\Editor\Interface\CrudEditorFormInterface;
use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractFieldConstructor;
use Module\Dashboard\Foundation\Admin\Controller\Users\Process\OnCreateSubmit;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Resource\Facade\Position;

class CreateController extends AbstractFieldConstructor
{
    protected function composeMicroApplication(): void
    {
        $this->enableDocumentMenu('main:users.create');
        parent::composeMicroApplication();
        $this->generateNotificationCheckbox();

        $this->crudEditor->getForm()->addSubmitAction(
            'user:create',
            new OnCreateSubmit($this->client, $this->crudEditor)
        );
        
        $this->crudEditor->getForm()->handleSubmission()
        ->then(function() {
            
        });
    }

    protected function generateNotificationCheckbox(): void
    {
        $field = $this->generateField([
            'nodeType' => Field::TYPE_CHECKBOX,
            'position' => Position::BEFORE,
            'position-target' => CrudEditorFormInterface::SUBMIT_KEY,
            'name' => 'notify_client',
            'label' => 'Send email to user after registration',
            //'collection-target' => 'avatar',
        ]);

        $context = $field->getElementContext();
        $context->widget->setRequired(false);
        $context->label->addClass('small');
        $context->frame->addClass('border-bottom mb-2 pb-3');
    }
}