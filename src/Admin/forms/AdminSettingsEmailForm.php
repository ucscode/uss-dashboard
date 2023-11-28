<?php

use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

class AdminSettingsEmailForm extends AbstractDashboardForm
{
    public function buildForm(): void
    {
        $uss = Uss::instance();

        $this->addField(
            'company[email]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_EMAIL))
                ->setInfoMessage("This is the official email address that will be used to send email to members")
                ->setWidgetValue($uss->options->get("company:email"))
        );

        $this->addField(
            'company[email-alt]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_EMAIL))
                ->setInfoMessage("This is what the client will see as incoming email (if specified)")
                ->setLabelValue("No-Reply Email")
                ->setWidgetValue($uss->options->get("company:email-alt"))
                ->setRequired(false)
        );

        $value = $uss->options->get("smtp:state");

        $smtpField = new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_RADIO);
        $smtpField
            ->setLabelValue("Use Default Settings")
            ->setWidgetChecked($value === 'default' || empty($value))
            ->setWidgetValue('default');

        $smtpField
            ->createSecondaryField("field1", UssForm::TYPE_RADIO)
            ->setLabelValue("Use SMTP Settings")
            ->setWidgetAttribute("name", "smtp[state]")
            ->setWidgetValue('custom')
            ->setWidgetChecked($value === 'custom');

        $this->addField(
            'smtp[state]',
            $smtpField
        );

        /**
         * SMTP Fieldstack
         */
        $this->addFieldStack("SMTP")
            ->setOuterContainerAttribute("class", "border-top p-3 mt-3", true)
            ->setTitleValue("SMTP Configuration")
            ->setInstructionValue('Simple Mail Transfer Protocol (SMTP), is a standardized communication protocol used for sending and receiving email messages between servers and email clients')
            ->removeInstructionAttribute("class", "alert-info")
            ->setInstructionAttribute("class", "fs-13px alert-warning", true)
            ->setFieldStackDisabled($value !== 'custom');

        $this->addField(
            'smtp[server]',
            (new UssFormField())
                ->setLabelValue("SMTP Server")
                ->setInfoMessage("Mail server responsible for sending outgoing emails")
                ->setWidgetAttribute("placeholder", "smtp.example.com")
                ->setWidgetValue($uss->options->get("smtp:server"))
        );

        $this->addField(
            'smtp[username]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_EMAIL))
                ->setLabelValue("SMTP Username")
                ->setInfoMessage("The unique identifier for accessing the SMTP server")
                ->setWidgetAttribute("placeholder", "user@example.com")
                ->setWidgetValue($uss->options->get("smtp:username"))
        );

        $this->addField(
            'smtp[password]',
            (new UssFormField())
                ->setLabelValue("SMTP Password")
                ->setInfoMessage("The confidential code to verify the user identity")
                ->setWidgetAttribute("placeholder", "****")
                ->setWidgetValue($uss->options->get("smtp:password"))
        );

        $this->addField(
            'smtp[port]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_NUMBER))
                ->setLabelValue("SMTP Port")
                ->setInfoMessage("The specific communication endpoint on the server")
                ->setWidgetAttribute("placeholder", "587")
                ->setWidgetValue($uss->options->get("smtp:port"))
        );

        $this->addField(
            'smtp[security]',
            (new UssFormField(UssForm::NODE_SELECT))
                ->setLabelValue("SMTP Security")
                ->setInfoMessage("The encryption protocol for secure communication")
                ->setWidgetOptions([
                    "TLS" => "TLS",
                    "SSL" => "SSL"
                ])
                ->setWidgetValue($uss->options->get("smtp:security"))
        );
    }
}