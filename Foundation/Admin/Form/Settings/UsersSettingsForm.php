<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings;

use Module\Dashboard\Foundation\Admin\Form\Settings\Abstract\AbstractUsersSettingsForm;

class UsersSettingsForm extends AbstractUsersSettingsForm
{
    public function buildForm(): void
    {
        $this->createSignupDisabledField();
        $this->createCollectUsernameField();
        $this->createConfirmEmailField();
        $this->createReadonlyEmailField();
        $this->createReconfirmEmailField();

        // foreach($this->getFieldStack("default")->getFields() as $field) {
        //     $field->inverse(true)
        //         ->createSecondaryField("alt")
        //         ->setWidgetValue(0);
        // }

        // /**
        //  * Fieldstack
        //  */
        // $this->addFieldStack("section-2");

        // $this->addField(
        //     'user[remove-inactive-after-day]',
        //     (new FormField(Form::NODE_INPUT, Form::TYPE_NUMBER))
        //         ->setLabelValue("Delete unconfirmed account after")
        //         ->setWidgetSuffix("days")
        //         ->setInfoMessage("Set to zero (0) to avoid deleting unconfirmed account")
        //         ->setWidgetValue($uss->options->get("user:remove-inactive-after-day") ?? 3)
        // );

        // /**
        //  * Field Stack
        //  */
        // $this->addFieldStack("section-3");

        // /**
        //  * This field is fully configured in `AdminSettingsUserController` class
        //  */
        // $this->addField(
        //     'user[default-roles][]',
        //     (new FormField(Form::NODE_INPUT, Form::TYPE_CHECKBOX))
        // );

        // $this->setSecurityHash();
    }

    protected function validateResource(array $filteredResource): ?array
    {
        return null;
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {
        
    }
}