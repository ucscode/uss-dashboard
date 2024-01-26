<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Gadget\Gadget;
use Ucscode\UssForm\Resource\Facade\Position;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

abstract class AbstractEmailSettingsForm extends AbstractDashboardForm
{
    protected Uss $uss;
    protected Collection $smtpCollection;

    public function createEmailCollectionFields(): void
    {   
        $this->collection->getElementContext()->fieldset
            ->setAttribute('id', 'collection-primary');
            
        $this->generateField('company[email]', [
            'nodeType' => Field::TYPE_EMAIL,
            'info' => 'The server email address that will send email to recipients',
            'info-class' => 'text-muted',
            'value' => $this->uss->options->get("company:email"),
        ]);
        
        $this->generateField('company[email-alt]', [
            'nodeType' => Field::TYPE_EMAIL,
            'label' => 'Display Email',
            'info' => 'The email address that will be displayed to the recipient (if specified)',
            'info-class' => 'text-muted',
            'value' => $this->uss->options->get("company:email-alt"),
            'required' => false,
        ]);

    }

    protected function createSMTPCollectionFields(): void
    {
        $smtpEnabled = $this->uss->options->get("smpt:enabled") == 1;

        $this->smtpCollection = new Collection();
        $this->addCollection('smtp', $this->smtpCollection);
        
        $collectionContext = $this->smtpCollection->getElementContext();
        $collectionContext->title->setValue('SMTP Configuration');
        $collectionContext->instruction->setValue(
            'Simple Mail Transfer Protocol (SMTP), is a standardized communication protocol used for sending and receiving email messages between servers and email clients'
        );
        $collectionContext->fieldset->setAttribute('id', 'collection-smtp');    

        $predigree = $this->generateField('smtp[enabled]', [
            'nodeType' => Field::TYPE_SWITCH,
            'label' => "Enable SMTP",
            'value' => 1,
            'checked' => $smtpEnabled,
            'required' => false,
            'widget-attributes' => [
                'id' => 'smtp-enable-checker'
            ]
        ], $this->smtpCollection);

        $gadget = new Gadget(Field::NODE_INPUT, Field::TYPE_HIDDEN);
        $gadget->widget
            ->setRequired(false)
            ->setValue(0)
        ;

        $predigree->field->addGadget('smtp[enabled]', $gadget);
        $predigree->field->setGadgetPosition($gadget, Position::BEFORE, $predigree->gadget);

        $this->generateField('smtp[server]', [
            'info' => 'Mail server responsible for sending outgoing emails',
            'widget-attributes' => [
                'placeholder' => 'smtp.example.com'
            ],
            'label' => 'SMTP Server',
            'value' => $this->uss->options->get('smtp:server'),
        ], $this->smtpCollection);

        $this->generateField('smtp[username]', [
            'label' => "SMTP Username",
            'info' => "The unique identifier for accessing the SMTP server",
            'widget-attributes' => [
                "placeholder", "user@example.com"
            ],
            'value' => $this->uss->options->get("smtp:username"),
        ], $this->smtpCollection);

        $this->generateField('smtp[password]', [
            'label' => "SMTP Password",
            'info' => "The confidential code to verify the user identity",
            'widget-attributes' => [
                "placeholder" => "****"
            ],
            'value' => $this->uss->options->get("smtp:password")
        ], $this->smtpCollection);

        $this->generateField('smtp[port]', [
            'nodeType' => Field::TYPE_NUMBER,
            'label' => "SMTP Port",
            'info' => "The specific communication endpoint on the server",
            'widget-attributes' => [
                'placeholder' => '587',
            ],
            'value' => $this->uss->options->get("smtp:port")
        ], $this->smtpCollection);

        $this->generateField('smtp[security]', [
            'nodeName' => Field::NODE_SELECT,
            'label' => "SMTP Security",
            'info' => "The encryption protocol for secure communication",
            'options' => [
                "TLS" => "TLS",
                "SSL" => "SSL"
            ],
            'value' => $this->uss->options->get("smtp:security"),
        ], $this->smtpCollection);

        // $this->setSecurityHash();
    }

    protected function createOtherFields(): void
    {
        $nonce = $this->uss->nonce($_SESSION[UssImmutable::SESSION_KEY]);

        $this->generateField('nonce', [
            'nodeType' => Field::TYPE_HIDDEN,
            'value' => $nonce,
        ]);

        $this->generateField('submit', [
            'fixed' => true,
            'nodeName' => Field::NODE_BUTTON,
            'nodeType' => Field::TYPE_SUBMIT,
            'content' => 'Save Changes',
        ], $this->smtpCollection);
    }
}