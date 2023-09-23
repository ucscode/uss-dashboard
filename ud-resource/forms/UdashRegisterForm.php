<?php

use Ucscode\UssForm\UssForm;
use Ucscode\SQuery\SQuery;

class UdashRegisterForm extends AbstractUdashForm
{
    protected User $user;

    protected function buildForm()
    {

        if(0) {
            $this->add('user[username]', UssForm::INPUT, UssForm::TYPE_TEXT, $this->style + [
                'attr' => [
                    'placeholder' => 'Username',
                    'pattern' => '^\s*\w+\s*$'
                ]
            ]);
        };

        $this->add('user[email]', UssForm::INPUT, UssForm::TYPE_EMAIL, $this->style + [
            'attr' => [
                'placeholder' => 'Email'
            ]
        ]);

        $this->add('user[password]', UssForm::INPUT, UssForm::TYPE_PASSWORD, $this->style + [
            'attr' => [
                'placeholder' => 'Password',
                'pattern' => '^.{4,}$'
            ]
        ]);

        $this->add('user[confirmPassword]', UssForm::INPUT, UssForm::TYPE_PASSWORD, $this->style + [
            'attr' => [
                'placeholder' => 'Confirm Password',
                'pattern' => '^.{4,}$'
            ]
        ]);

        $this->addRow('my-2');

        $this->add('user[agreement]', UssForm::INPUT, UssForm::TYPE_CHECKBOX, $this->style + [
            'required' => true,
            'label' => "I agree to the Terms of service Privacy policy",
            'class_label' => null,
            'ignore' => true
        ]);

        $this->addRow();

        $this->add('submit', UssForm::BUTTON, UssForm::TYPE_SUBMIT, $this->style + [
            'class' => 'w-100 btn btn-primary'
        ]);

    }

    public function isValid(?array $post = null): bool
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

    public function prepareEntryData(array $post): array
    {
        unset($post['user']['confirmPassword']);
        $post['user']['email'] = strtolower($post['user']['email']);
        $post['user']['password'] = password_hash($post['user']['password'], PASSWORD_DEFAULT);
        $post['user']['usercode'] = Core::keygen(7);
        return $post;
    }

    public function persistEntry(array $data): bool
    {
        $this->user = new User();
        $this->user->email = $data['user']['email'];
        $this->user->password = $data['user']['password'];
        $this->user->usercode = $data['user']['usercode'];
        $this->user->username = $data['user']['username'] ?? null;
        return $this->user->persist();
    }

    public function onEntrySuccess(array $post): void
    {
        $this->user->set('role', ['MEMBER']);

        (new Alert("Your registration was successful"))
            ->type('notification')
            ->followRedirectAs('ud-registration')
            ->display('success');
        
        (new Alert("Please confirm the link sent to your email"))
            ->type('notification')
            ->followRedirectAs('ud-email')
            ->display('info', 2000);
        
        $this->sendEmail();

        header("location: " . Udash::instance()->url());

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
    protected function validateEmail(string $email)
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if(!$email) {
            $this->setReport('user[email]', "Invalid email address");
            return false;
        } else {
            $exists = Udash::instance()->easyQuery(DB_PREFIX . "users", $email, 'email');
            if($exists) {
                $this->setReport('user[email]', 'The email address already exists');
                return false;
            };
        };
        return $email;
    }

    protected function validatePassword(string $password, string $confirmPassword)
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

    protected function sendEmail() {
        
    }

}
