<?php

use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

class UserRegisterForm extends AbstractDashboardForm
{
    protected User $user;

    protected function buildForm()
    {
        if(0) {
            $this->addField(
                'user[username]',
                (new UssFormField())
                    ->setWidgetAttribute('placeholder', 'Username')
                    ->setWidgetAttribute('pattern', '^\s*\w+\s*$')
                    ->setLabelHidden(true)
            );
        };

        $this->addField(
            'user[email]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_EMAIL))
                ->setWidgetAttribute('placeholder', 'Email')
                ->setLabelHidden(true)
        );

        $this->addField(
            'user[password]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_PASSWORD))
                ->setWidgetAttribute('placeholder', 'Password')
                ->setWidgetAttribute('pattern', '^.{4,}$')
                ->setLabelHidden(true)
        );

        $this->addField(
            'user[confirmPassword]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_PASSWORD))
                ->setWidgetAttribute('placeholder', 'Confirm Password')
                ->setWidgetAttribute('pattern', '^.{4,}$')
                ->setLabelHidden(true)
        );

        $this->getFieldStack('default')
            ->setOuterContainerAttribute('class', 'mb-2', true);

        $this->addFieldStack();

        $this->addField(
            'user[agreement]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_CHECKBOX))
                ->setLabelValue("I agree to the Terms of service Privacy policy")
                ->setRowAttribute('class', 'mb-2 user-select-none small', true),
            ['mapped' => false]
        );

        $this->addField(
            'submit',
            (new UssFormField(UssForm::NODE_BUTTON, UssForm::TYPE_SUBMIT))
                ->setWidgetAttribute('class', 'w-100', true)
        );
    }

    public function isValid(array $post): bool
    {
        $user = $post['user'] ?? [];
        $approved =
            !empty($user)
            && $this->validateEmail($user['email'])
            && $this->validatePassword($user['password'], $user['confirmPassword']);
        return $approved;
    }

    public function handleInvalidRequest(?array $post): void
    {
        unset($post['user']['password']);
        unset($post['user']['confirmPassword']);
        $this->populate($post);
    }

    public function persistEntry(array $data): bool
    {
        $data = $this->filterData($data);
        $this->user = new User();
        foreach($data['user'] as $key => $value) {
            call_user_func(
                [$this->user, "set" . ucfirst($key)],
                $value ?: null
            );
        };
        return $this->user->persist();
    }

    public function onEntrySuccess(array $post): void
    {
        $this->user->setUserMeta('user.roles', [RoleImmutable::ROLE_USER]);

        (new Alert("Your registration was successful"))
            ->type('notification')
            ->followRedirectAs('ud-registration')
            ->display('success');

        $this->sendEmail();

        header("location: " . UserDashboard::instance()->urlGenerator('/'));

        exit;
    }

    public function onEntryFailure(array $post): void
    {
        (new Alert('Sorry! The registration failed'))
            ->type('notification')
            ->display('error');
    }

    /**
     * [VALIDATION] METHODS
     *
     * @ignore
     */
    protected function validateEmail(string $email): bool|string
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if(!$email) {
            $this->getField('user[email]')
                ->setValidationMessage("Invalid email address");
            return false;
        } else {
            $exists = Uss::instance()->fetchItem(DB_PREFIX . "users", $email, 'email');
            if($exists) {
                $this->getField('user[email]')
                    ->setValidationMessage('The email address already exists');
                return false;
            };
        };
        return $email;
    }

    protected function validatePassword(string $password, string $confirmPassword): bool
    {
        if(strlen($password) < 6) {
            $this->getField('user[password]')
                ->setValidationMessage("Password should be at least 6 characters");
            return false;
        } elseif($password !== $confirmPassword) {
            $this->getField('user[confirmPassword]')
                ->setValidationMessage("Confirm password does not match");
            return false;
        };
        return true;
    }

    public function filterData(array $post): array
    {
        unset($post['user']['confirmPassword']);
        $post['user']['email'] = strtolower($post['user']['email']);
        $post['user']['password'] = password_hash($post['user']['password'], PASSWORD_DEFAULT);
        $post['user']['usercode'] = Uss::instance()->keygen(7);
        return $post;
    }

    protected function sendEmail()
    {
        (new Alert("Please confirm the link sent to your email"))
            ->type('notification')
            ->followRedirectAs('ud-email')
            ->display('info', 2000);
    }

}
