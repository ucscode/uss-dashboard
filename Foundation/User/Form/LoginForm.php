<?php

namespace Module\Dashboard\Foundation\User\Form;

use Ucscode\Form\Form;
use Ucscode\UssElement\UssElement;
use Ucscode\Form\FormField;
use Module\Dashboard\Bundle\Kernel\AbstractDashboardForm;
use Module\Dashboard\Bundle\User\User;

class LoginForm extends AbstractDashboardForm
{
    private string $error;
    protected User $user;

    public function __construct( $formContext)
    {
        parent::__construct($formContext);
    }

    protected function buildForm()
    {
        $this->addField(
            'user[login]',
            (new FormField())
                ->setWidgetAttribute('placeholder', 'login Detail')
                ->setWidgetAttribute(
                    'pattern',
                    '^\s*(?:\w+|(?:[^@]+@[a-zA-Z0-9\-_]+\.\w{2,}))\s*$'
                )
                ->setLabelHidden(true)
        );

        $this->addField(
            'user[password]',
            (new FormField(Form::NODE_INPUT, Form::TYPE_PASSWORD))
                ->setWidgetAttribute('placeholder', 'Password')
                ->setWidgetAttribute('pattern', '^.{4,}$')
                ->setLabelHidden(true)
        );

        $this->addCustomElement('mail-element', $this->buildMailBlock());

        $this->addField(
            'submit',
            (new FormField(Form::NODE_BUTTON, Form::TYPE_SUBMIT))
                ->setWidgetAttribute('class', 'w-100', true)
        );

    }

    public function handleSubmission(): void
    {
        $this->user = new User();
        if(!$this->user->getFromSession()) {
            parent::handleSubmission();
        };
    }

    public function persistEntry(array $data): bool
    {
        $column = strpos($data['user']['login'], '@') === false ? 'username' : 'email';

        $this->user = (new User())->allocate($column, $data['user']['login']);

        if($this->user->exists()) {
            if($this->user->isValidPassword($data['user']['password'])) {
                return true;
            };
        };

        if(isset($data['user']['password'])) {
            unset($data['user']['password']);
        };

        $this->populate($data);
        return !($this->error = "Authentication Failed: Incorrect login credential");
    }

    public function onEntryFailure(array $data): void
    {
        (new Alert($this->error))
            ->type('notification')
            ->display('error');
    }

    public function onEntrySuccess(array $data): void
    {
        $this->user->setLastSeen(new DateTime('now'));
        $this->user->persist();
        $this->user->saveToSession();

        (new Alert("Authentication Successful"))
            ->type('notification')
            ->display('success');
    }

    protected function buildMailBlock()
    {
        $div1 = (new UssElement(Form::NODE_DIV))
            ->setAttribute('class', 'd-flex justify-content-between my-3 col-12')
            ->setAttribute('id', 'reactive-mailer');
        $div2 = (new UssElement(Form::NODE_DIV))
            ->setAttribute('class', 'resend-email ms-auto');
        $a = (new UssElement(Form::NODE_A))
            ->setAttribute('href', 'javascript:void(0)')
            ->setAttribute('title', 'Resend Confirmation Email')
            ->setAttribute('data-vcode');

        $small = (new UssElement(Form::NODE_SMALL))
            ->setContent('Reconfirm Email');
        $i = (new UssElement(Form::NODE_I))
            ->setAttribute('class', 'bi bi-envelope-at');

        $div1->appendChild($div2);
        $div2->appendChild($a);
        $a->appendChild($small);
        $a->appendChild($i);

        return $div1;
    }

}
