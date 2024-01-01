<?php

namespace Module\Dashboard\Foundation\User\Form;

use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Field\Field;

class LoginForm extends AbstractUserAccountForm
{
    public readonly UssElement $mailerBlock;

    public function buildForm(): void
    {
        $this->createAccessField();
        $this->createPasswordField();
        $agreementField = $this->createAgreementCheckboxField();
        $this->createSubmitButton();

        $this->createMailerBlock($agreementField);
        $this->hideLabels();
    }
    
    /**
     * @Build
     */
    protected function createAccessField(): Field
    {
        [$field, $context] = $this->getFieldVariation();

        $field->getElementContext()->widget
            ->setAttribute('placeholder', 'Email / Username')
            ->setAttribute('pattern', '^\s*(?:\w+|(?:[^@]+@[a-zA-Z0-9\-_]+\.\w{2,}))\s*$')
            ;

        $context->prefix->setValue("<i class='bi bi-person-check-fill'></i>");

        $this->collection->addField("user[access]", $field);

        return $field;
    }

    /**
     * @Build
     */
    protected function createMailerBlock(Field $submitField): void
    {
        $this->mailerBlock = new UssElement(UssElement::NODE_DIV);

        $htmlContent = "
            <div class='resend-email ms-auto'>
                <a href='javascript:void(0)' title='Resend Confirmation Email' data-vcode>
                    <small>Reconfirm Email</small> <i class='bi bi-envelope-at'></i>
                </a>
            </div>
        ";

        $this->mailerBlock
            ->setContent($htmlContent)
            ->setAttribute('class', 'd-flex justify-content-between my-1 col-12')
            ->setAttribute('id', 'mailer-block');


        $element = $submitField->getElementContext()->frame->getElement();
        $element->getParentElement()->insertBefore(
            $this->mailerBlock,
            $element
        );
    }
}
