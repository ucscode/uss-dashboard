<?php

use Ucscode\UssForm\UssForm;
use Ucscode\UssElement\UssElement;

class UdashLoginForm extends AbstractUdashForm
{
    protected function buildForm()
    {

        $this->add('login', UssForm::INPUT, UssForm::TYPE_TEXT, $this->style + [
            'attr' => [
                'placeholder' => 'Login detail',
                'pattern' => '^\s*(?:\w+|(?:[^@]+@[a-zA-Z0-9\-_]+\.\w{2,}))\s*$',
                'required'
            ]
        ]);

        $this->add('password', UssForm::INPUT, UssForm::TYPE_PASSWORD, $this->style + [
            'attr' => [
                'placeholder' => 'Password',
                'pattern' => '^.{4,}$',
                'required'
            ]
        ]);

        $this->addRow();

        $this->appendField($this->buildMailBlock());

        $this->add('submit', UssForm::BUTTON, UssForm::TYPE_SUBMIT, [
            'class' => 'btn btn-primary w-100'
        ]);

    }

    public function handleSubmission(): void
    {
    }

    protected function buildMailBlock()
    {

        # Create Block;

        $div1 = (new UssElement(UssForm::NODE_DIV))
            ->setAttribute('class', 'd-flex justify-content-between my-3 col-12');

        $div2 = (new UssElement(UssForm::NODE_DIV))
            ->setAttribute('class', 'resend-email ms-auto');

        $a = (new UssElement(UssForm::NODE_A))
            ->setAttribute('href', 'javascript:void(0)')
            ->setAttribute('title', 'Resend Confirmation Email')
            ->setAttribute('data-vcode');

        $small = (new UssElement(UssForm::NODE_SMALL))
            ->setContent('Reconfirm Email');

        $i = (new UssElement(UssForm::NODE_I))
            ->setAttribute('class', 'bi bi-envelope-at');

        # Organize Block

        $div1->appendChild($div2);
        $div2->appendChild($a);
        $a->appendChild($small);
        $a->appendChild($i);

        return $div1;

    }

}
