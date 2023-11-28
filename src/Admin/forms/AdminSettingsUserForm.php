<?php

use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

class AdminSettingsUserForm extends AbstractDashboardForm
{
    public function buildForm(): void
    {
        $uss = Uss::instance();

        $this->addField(
            'user[disable-signup]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_SWITCH))
                ->setLabelValue("Temporarily disable signup")
                ->setInfoMessage("Disallow registration until this option is turned off")
                ->setWidgetChecked(!empty($uss->options->get("user:disable-signup")))
                ->setRequired(false)
        );

        $this->addField(
            'user[collect-username]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_SWITCH))
                ->setLabelValue("Collect username at signup")
                ->setInfoMessage("Hide or display the username input in the registration form")
                ->setWidgetChecked(!empty($uss->options->get("user:collect-username")))
                ->setRequired(false)
        );

        $this->addField(
            'user[confirm-email]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_SWITCH))
                ->setLabelValue("Send confirmation email after registration")
                ->setInfoMessage("Enforce user to confirm their email before they can login")
                ->setWidgetChecked(!empty($uss->options->get("user:confirm-email")))
                ->setRequired(false)
        );

        $this->addField(
            'user[lock-email]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_SWITCH))
                ->setLabelValue("Lock user email")
                ->setInfoMessage("Prevent user from changing their email after login")
                ->setWidgetChecked(!empty($uss->options->get("user:lock-email")))
                ->setRequired(false)
        );

        $this->addField(
            'user[reconfirm-email]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_SWITCH))
                ->setLabelValue("Resend confirmation email after update")
                ->setInfoMessage("Enforce user to confirm their new email address")
                ->setWidgetChecked(!empty($uss->options->get("user:reconfirm-email")))
                ->setRequired(false)
        );

        /**
         * Fieldstack
         */
        $this->addFieldStack("section-2");

        $this->addField(
            'user[remove-inactive-after-day]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_NUMBER))
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
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_CHECKBOX))
        );
    }
}