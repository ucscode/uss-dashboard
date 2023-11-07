<?php

use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

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
        if($this->stage === self::STAGE_EMAIL) {

            // email stage
            $this->addField(
                'email',
                (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_EMAIL))
                    ->setWidgetAttribute('placeholder', 'Enter your account email')
            );

        } else {

            // password reset stage
            $this->addField(
                'user[password]',
                (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_PASSWORD))
                    ->setLabelValue('New Password')
                    ->setWidgetAttribute('placeholder', 'Enter your new password')
            );

            $this->addField(
                'user[confirm_password]',
                (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_PASSWORD))
                    ->setLabelValue('Confirm Password')
                    ->setWidgetAttribute('placeholder', 'Confirm your new password')
            );

            $this->addField(
                'user[id]',
                (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_HIDDEN))
                    ->setWidgetValue($this->user->getId())
            );
        }

        $this->addField(
            'submit',
            (new UssFormField(UssForm::NODE_BUTTON, UssForm::TYPE_SUBMIT))
                ->setWidgetAttribute('class', 'w-100', true)
        );
    }

    public function isValid(array $data): bool
    {
        if($this->stage === self::STAGE_EMAIL) {

            $this->reportKey = 'email';
            $this->reportError = "The email address is not valid";
            return filter_var($data['email'], FILTER_VALIDATE_EMAIL);

        } else {

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
        if($this->stage === self::STAGE_EMAIL) {
            $this->populate($data);
        };

        $this->getField($this->reportKey)
            ->setValidationMessage($this->reportError)
            ;
    }

    public function persistEntry(array $data): bool
    {
        if($this->stage === self::STAGE_EMAIL) {

            $this->user = new User();
            $this->user->allocate('email', $data['email']);
            return $this->user->exists();

        } else {

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
        if($this->stage === self::STAGE_EMAIL) {
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
        if($this->stage === self::STAGE_EMAIL) {
            $this->getField('email')
                ->setValidationMessage('We could find the email in our database');
        }
    }

}
