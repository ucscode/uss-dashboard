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
        $this->createSubmitButton();
        $this->createMailerBlock();
        $this->hideLabels();
    }
    
    /**
     * @Build
     */
    protected function createAccessField(): void
    {
        $field = new Field();

        $field->getElementContext()->widget
            ->setAttribute('placeholder', 'login detail')
            ->setAttribute('pattern', '^\s*(?:\w+|(?:[^@]+@[a-zA-Z0-9\-_]+\.\w{2,}))\s*$')
            ;

        $this->collection->addField("user[access]", $field);
    }

    /**
     * @Build
     */
    protected function createMailerBlock(): void
    {
        $this->mailerBlock = new UssElement(UssElement::NODE_DIV);

        $htmlContent = "
            <div class='d-flex justify-content-between my-3 col-12' id='reactive-mailer'>
                <div class='resend-email ms-auto'>
                    <a
                        href='javascrip:void(0)'
                        title='Resend Confirmation Email'
                        data-vcode
                    >
                        <small>Reconfirm Email</small>
                        <i class='bi bi-envelope-at'></i>
                    </a>
                </div>
            </div>
        ";

        $this->mailerBlock->setContent($htmlContent);
    }
}
