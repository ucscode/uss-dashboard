<?php

use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

class AdminSettingsEmailForm extends AbstractDashboardForm
{
    public function buildForm(): void
    {
        $this->addField(
            'company[email]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_EMAIL))
                ->setInfoMessage("This is the official email address that will be used to send email to members")
        );

        $this->addField(
            'company[email-alt]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_EMAIL))
                ->setInfoMessage("This is what the client will see as incoming email (if specified)")
                ->setLabelValue("No-Reply Email")
        );

        $smtpField = new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_RADIO);
        $smtpField
            ->setLabelValue("Use Default Settings")
            ->setWidgetChecked(true);

        $smtpField
            ->createSecondaryField("field1", UssForm::TYPE_RADIO)
            ->setLabelValue("Use SMTP Settings")
            ->setWidgetAttribute("name", "smtp[state]");

        $this->addField(
            'smtp[state]',
            $smtpField
        );

        $this->addFieldStack("SMTP")
            ->setTitleValue("SMTP Config")
            ->setSubtitleValue('What is the consequence of this');

        $this->addField(
            'smtp[server]',
            (new UssFormField())
        );

        $this->addField(
            'smtp[username]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_EMAIL))
        );

        $this->addField(
            'smtp[password]',
            (new UssFormField())
        );

        $this->addField(
            'smtp[port]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_NUMBER))
        );

        $this->addField(
            'smtp[security]',
            (new UssFormField())
        );
    }
}