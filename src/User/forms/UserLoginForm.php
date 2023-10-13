<?php

use Ucscode\UssForm\UssForm;
use Ucscode\UssElement\UssElement;

class UserLoginForm extends AbstractUdForm
{
    private string $error;
    protected ?array $user;

    protected function buildForm()
    {

        $this->add('user[login]', UssForm::INPUT, UssForm::TYPE_TEXT, $this->style + [
            'attr' => [
                'placeholder' => 'Login detail',
                'pattern' => '^\s*(?:\w+|(?:[^@]+@[a-zA-Z0-9\-_]+\.\w{2,}))\s*$',
                'required'
            ]
        ]);

        $this->add('user[password]', UssForm::INPUT, UssForm::TYPE_PASSWORD, $this->style + [
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
        $user = new User();
        if(!$user->getFromSession()) {
            parent::handleSubmission();
        };
    }

    public function persistEntry(array $data): bool
    {
        $column = strpos($data['user']['login'], '@') === false ? 'username' : 'email';

        $this->user = (new UssUtils())->fetchData(User::TABLE, $data['user']['login'], $column);

        if(!empty($this->user)) {
            $isValidPassword = password_verify($data['user']['password'], $this->user['password']);
            if($isValidPassword) {
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
        (new User($this->user['id']))->saveToSession();

        (new Alert("Authentication Successful"))
            ->type('notification')
            ->display('success');

    }

    protected function buildMailBlock()
    {
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

        $div1->appendChild($div2);
        $div2->appendChild($a);
        $a->appendChild($small);
        $a->appendChild($i);

        return $div1;
    }

}
