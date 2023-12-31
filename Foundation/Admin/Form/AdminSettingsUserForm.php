<?php

use Ucscode\Form\Form;
use Ucscode\Form\FormField;

class AdminSettingsUserForm extends AbstractDashboardForm
{
    protected function init(): void
    {
        $this->handleSubmission();
    }

    public function buildForm(): void
    {
        $uss = Uss::instance();

        $this->addField(
            'user[disable-signup]',
            (new FormField(Form::NODE_INPUT, Form::TYPE_SWITCH))
                ->setLabelValue("Temporarily disable signup")
                ->setInfoMessage("Disallow registration until this option is turned off")
                ->setWidgetChecked(!empty($uss->options->get("user:disable-signup")))
                ->setRequired(false)
                ->setWidgetValue(1)
        );

        $this->addField(
            'user[collect-username]',
            (new FormField(Form::NODE_INPUT, Form::TYPE_SWITCH))
                ->setLabelValue("Collect username at signup")
                ->setInfoMessage("Hide or display the username input in the registration form")
                ->setWidgetChecked(!empty($uss->options->get("user:collect-username")))
                ->setRequired(false)
                ->setWidgetValue(1)
        );

        $this->addField(
            'user[confirm-email]',
            (new FormField(Form::NODE_INPUT, Form::TYPE_SWITCH))
                ->setLabelValue("Send confirmation email after registration")
                ->setInfoMessage("Enforce user to confirm their email before they can login")
                ->setWidgetChecked(!empty($uss->options->get("user:confirm-email")))
                ->setRequired(false)
                ->setWidgetValue(1)
        );

        $this->addField(
            'user[lock-email]',
            (new FormField(Form::NODE_INPUT, Form::TYPE_SWITCH))
                ->setLabelValue("Lock user email")
                ->setInfoMessage("Prevent user from changing their email after login")
                ->setWidgetChecked(!empty($uss->options->get("user:lock-email")))
                ->setRequired(false)
                ->setWidgetValue(1)
        );

        $this->addField(
            'user[reconfirm-email]',
            (new FormField(Form::NODE_INPUT, Form::TYPE_SWITCH))
                ->setLabelValue("Resend confirmation email after update")
                ->setInfoMessage("Enforce user to confirm their new email address")
                ->setWidgetChecked(!empty($uss->options->get("user:reconfirm-email")))
                ->setRequired(false)
                ->setWidgetValue(1)
        );

        foreach($this->getFieldStack("default")->getFields() as $field) {
            $field->inverse(true)
                ->createSecondaryField("alt")
                ->setWidgetValue(0);
        }

        /**
         * Fieldstack
         */
        $this->addFieldStack("section-2");

        $this->addField(
            'user[remove-inactive-after-day]',
            (new FormField(Form::NODE_INPUT, Form::TYPE_NUMBER))
                ->setLabelValue("Delete unconfirmed account after")
                ->setWidgetSuffix("days")
                ->setInfoMessage("Set to zero (0) to avoid deleting unconfirmed account")
                ->setWidgetValue($uss->options->get("user:remove-inactive-after-day") ?? 3)
        );

        /**
         * Field Stack
         */
        $this->addFieldStack("section-3");

        /**
         * This field is fully configured in `AdminSettingsUserController` class
         */
        $this->addField(
            'user[default-roles][]',
            (new FormField(Form::NODE_INPUT, Form::TYPE_CHECKBOX))
        );

        $this->setSecurityHash();
    }

    public function persistEntry(array $data): bool
    {
        $result = [];
        foreach($data['user'] as $key => $value) {
            $basis = "user:{$key}";
            if(is_numeric($value)) {
                $value = (float)$value;
            }
            $result[] = Uss::instance()->options->set($basis, $value);
        }
        return !in_array(false, $result, true);
    }

    public function onEntrySuccess(array $data): void
    {
        (new Alert("User settings successfully updated"))
            ->display();
    }

    public function onEntryFailure(array $data): void
    {
        (new Alert("User settings could not updated"))
            ->display();
    }
}