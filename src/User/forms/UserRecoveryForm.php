<?php

use Ucscode\UssForm\UssForm;

class UserRecoveryForm extends AbstractDashboardForm
{
    protected User $user;

    protected function buildForm()
    {
        $this->add(
            'email', 
            UssForm::NODE_INPUT, 
            UssForm::TYPE_EMAIL, 
            [
                'label' => "Email",
                'attr' => [
                    'placeholder' => 'Enter your account email',
                    'required'
                ]
            ]
        );

        $this->add(
            'submit',
            UssForm::NODE_BUTTON,
            UssForm::TYPE_SUBMIT,
            [
                'attr' => [
                    'class' => 'btn btn-primary w-100'
                ]
            ]
        );
    }

    public function isValid(array $data): bool
    {
        if(!empty($data['email'])) {
            return filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        }
        return false;
    }

    public function handleInvalidRequest(?array $data): void
    {
        $this->populate($data);
        if(!empty($data['email'])) {
            $this->setReport('email', "The email address is not valid");
        };
    }

    public function persistEntry(array $data): bool
    {
        $this->user = new User();
        $this->user->allocate('email', $data['email']);
        return $this->user->exists();
    }

    public function onEntrySuccess(array $data): void
    {
        var_dump($data);
    }

}
