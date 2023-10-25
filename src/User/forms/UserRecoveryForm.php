<?php

use Ucscode\UssForm\UssForm;

class UserRecoveryForm extends AbstractUserRecoveryForm
{
    protected ?string $reportKey = null;
    protected ?string $reportError = null;
    protected bool $passwordUpdated;

    protected function onCreate(): void
    {
        $this->defineStage();
    }

    protected function buildForm()
    {
        if(!$this->stage) {

            // email stage
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

        } else {

            // password reset stage
            $this->add(
                'user[password]',
                UssForm::NODE_INPUT,
                UssForm::TYPE_PASSWORD,
                [
                    'label' => 'New Password',
                    'attr' => [
                        'required',
                        'placeholder' => 'Enter your new password'
                    ]
                ]
            );

            $this->add(
                'user[confirm_password]',
                UssForm::NODE_INPUT,
                UssForm::TYPE_PASSWORD,
                [
                    'label' => 'Confirm Password',
                    'attr' => [
                        'required',
                        'placeholder' => 'Confirm your new password'
                    ]
                ]
            );

            $this->add(
                'user[id]',
                UssForm::NODE_INPUT,
                UssForm::TYPE_HIDDEN,
                [
                    'value' => $this->user->getId()
                ]
            );
        }

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
        if(!$this->stage) {

            // email stage
            $this->reportKey = 'email';
            $this->reportError = "The email address is not valid";
            return filter_var($data['email'], FILTER_VALIDATE_EMAIL);

        } else {

            // password reset stage
            if(strlen($data['user']['password']) < 6) {
                $this->reportKey = 'user[password]';
                $this->reportError = 'Password should be at least 6 characters';
            } elseif($data['user']['password'] !== $data['user']['confirm_password']) {
                $this->reportKey = 'user[confirm_password]';
                $this->reportError = 'Password does not match';
            }

        }

        return empty($this->reportKey);
    }

    public function handleInvalidRequest(?array $data): void
    {
        if(!$this->stage) {
            $this->populate($data);
        };
        $this->setReport($this->reportKey, $this->reportError);
    }

    public function persistEntry(array $data): bool
    {
        if(!$this->stage) {

            // email stage
            $this->user = new User();
            $this->user->allocate('email', $data['email']);
            return $this->user->exists();

        } else {

            // password reset stage
            $this->user = new User($data['user']['id'] ?? 0);
            $userExists = $this->user->exists();

            if($userExists) {
                $this->user->setPassword($data['user']['password'], true);
                $this->status = $this->user->persist();
            }

            return $userExists;
        }
    }

    public function onEntrySuccess(array $data): void
    {
        if(!$this->stage) {
            // email stage
            $this->status = $this->sendRecoveryEmail($this->user);
            if(!$this->status) {
                (new Alert('Email failed to send'))
                    ->type('notification')
                    ->display('warning');
            }
        } else {
            if($this->status) {
                $this->user->removeUserMeta('user.recovery_code');
            }
        }
    }

    public function onEntryFailure(array $data): void
    {
        $this->populate($data);
        if(!$this->stage) {
            $this->setReport('email', 'We could find the email in our database');
        }
    }

}
