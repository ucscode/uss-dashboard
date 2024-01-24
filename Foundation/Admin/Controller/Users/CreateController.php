<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractFieldConstructor;
use Module\Dashboard\Foundation\Admin\Controller\Users\Process\OnCreateSubmit;
use Module\Dashboard\Foundation\User\UserDashboard;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Resource\Facade\Position;
use Uss\Component\Kernel\Uss;

class CreateController extends AbstractFieldConstructor
{
    protected function composeMicroApplication(): void
    {
        $this->enableDocumentMenu('main:users.create');
        parent::composeMicroApplication();
        $this->generateNotificationCheckbox();

        $this->crudEditor->getForm()->setProperty(
            'crud:create.email.loginUrl',
            UserDashboard::instance()->getDocument('index')?->getUrl()
        );

        $submitAction = new OnCreateSubmit($this->client, $this->crudEditor, $this->dashboard);

        $promise = $this->crudEditor->getForm()
            ->addSubmitAction('user:create', $submitAction)
            ->handleSubmission();

        $promise->then(function(CrudEditorForm $form) {
            if($form->getPersistenceStatus()) {
                $redirectUrl = Uss::instance()->replaceUrlQuery([
                    'entity' => $form->getPersistenceLastInsertId(),
                    'channel' => CrudEnum::UPDATE->value,
                ]);
                header("location: {$redirectUrl}");
                die;
            }
        });
    }

    protected function generateNotificationCheckbox(): void
    {
        $field = $this->generateField([
            'nodeType' => Field::TYPE_CHECKBOX,
            'position' => Position::BEFORE,
            'position-target' => CrudEditorForm::SUBMIT_KEY,
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