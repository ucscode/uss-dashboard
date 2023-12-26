<?php

use Ucscode\UssForm\UssForm;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssFormField;

class UserLoginForm extends AbstractDashboardForm
{
    private string $error;
    protected User $user;

    protected function buildForm()
    {
        $this->addField(
            'user[login]',
            (new UssFormField())
                ->setWidgetAttribute('placeholder', 'login Detail')
                ->setWidgetAttribute(
                    'pattern',
                    '^\s*(?:\w+|(?:[^@]+@[a-zA-Z0-9\-_]+\.\w{2,}))\s*$'
                )
                ->setLabelHidden(true)
        );

        $this->addField(
            'user[password]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_PASSWORD))
                ->setWidgetAttribute('placeholder', 'Password')
                ->setWidgetAttribute('pattern', '^.{4,}$')
                ->setLabelHidden(true)
        );

        $this->addCustomElement('mail-element', $this->buildMailBlock());

        $this->addField(
            'submit',
            (new UssFormField(UssForm::NODE_BUTTON, UssForm::TYPE_SUBMIT))
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
        $div1 = (new UssElement(UssForm::NODE_DIV))
            ->setAttribute('class', 'd-flex justify-content-between my-3 col-12')
            ->setAttribute('id', 'reactive-mailer');
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

        $div1->appendChild($div2);
        $div2->appendChild($a);
        $a->appendChild($small);
        $a->appendChild($i);

        return $div1;
    }

}
