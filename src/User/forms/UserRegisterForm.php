<?php

use Ucscode\UssForm\UssForm;

class UserRegisterForm extends AbstractDashboardForm
{
    protected User $user;

    protected function buildForm()
    {

        if(0) {
            $this->add(
                'user[username]',
                UssForm::INPUT,
                UssForm::TYPE_TEXT,
                $this->style + [
                    'attr' => [
                        'placeholder' => 'Username',
                        'pattern' => '^\s*\w+\s*$'
                    ]
                ]
            );
        };

        $this->add(
            'user[email]',
            UssForm::INPUT,
            UssForm::TYPE_EMAIL,
            $this->style + [
                'attr' => [
                    'placeholder' => 'Email'
                ]
            ]
        );

        $this->add(
            'user[password]',
            UssForm::INPUT,
            UssForm::TYPE_PASSWORD,
            $this->style + [
                'attr' => [
                    'placeholder' => 'Password',
                    'pattern' => '^.{4,}$'
                ]
            ]
        );

        $this->add(
            'user[confirmPassword]',
            UssForm::INPUT,
            UssForm::TYPE_PASSWORD,
            $this->style + [
                'attr' => [
                    'placeholder' => 'Confirm Password',
                    'pattern' => '^.{4,}$'
                ]
            ]
        );

        $this->addRow('my-2');

        $this->add(
            'user[agreement]',
            UssForm::INPUT,
            UssForm::TYPE_CHECKBOX,
            array_merge($this->style, [
                'required' => true,
                'label' => "I agree to the Terms of service Privacy policy",
                'label_class' => null,
                'ignore' => true
            ])
        );

        $this->addRow();

        $this->add(
            'submit',
            UssForm::BUTTON,
            UssForm::TYPE_SUBMIT,
            $this->style + [
                'class' => 'w-100 btn btn-primary'
            ]
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
            $this->user->{$key} = $value ?: null;
        };
        return $this->user->persist();
    }

    public function onEntrySuccess(array $post): void
    {
        $this->user->setMeta('user.role', ['MEMBER']);

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
            $this->setReport('user[email]', "Invalid email address");
            return false;
        } else {
            $exists = Uss::instance()->fetchData(DB_PREFIX . "users", $email, 'email');
            if($exists) {
                $this->setReport('user[email]', 'The email address already exists');
                return false;
            };
        };
        return $email;
    }

    protected function validatePassword(string $password, string $confirmPassword): bool
    {
        if(strlen($password) < 6) {
            $this->setReport('user[password]', "Password should be at least 6 characters");
            return false;
        } elseif($password !== $confirmPassword) {
            $this->setReport('user[confirmPassword]', "Confirm password does not match");
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
