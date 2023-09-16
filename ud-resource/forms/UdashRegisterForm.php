<?php

use Ucscode\UssForm\UssForm;

class UdashRegisterForm extends AbstractUdashForm {

    protected function buildForm() {

        if(0) {
            $this->add('username', UssForm::INPUT, UssForm::TYPE_TEXT, $this->style + [
                'attr' => [
                    'placeholder' => 'Username',
                    'pattern' => '^\s*\w+\s*$'
                ]
            ]);
        };

        $this->add('email', UssForm::INPUT, UssForm::TYPE_EMAIL, $this->style + [
            'attr' => [
                'placeholder' => 'Email'            
            ]
        ]);

        $this->add('password', UssForm::INPUT, UssForm::TYPE_PASSWORD, $this->style + [
            'attr' => [
                'placeholder' => 'Password',
                'pattern' => '^.{4,}$'
            ]
        ]);

        $this->add('confirmPassword', UssForm::INPUT, UssForm::TYPE_PASSWORD, $this->style + [
            'attr' => [
                'placeholder' => 'Confirm Password',
                'pattern' => '^.{4,}$'
            ]
        ]);

        $this->addRow('my-2');

        $this->add('agreement', UssForm::INPUT, UssForm::TYPE_CHECKBOX, $this->style + [
            'required' => true,
            'label' => 'I agree to the Terms of service &amp; Privacy policy',
            'class_label' => null
        ]);

        $this->addRow();

        $this->add('submit', UssForm::BUTTON, UssForm::TYPE_SUBMIT, $this->style + [
            'class' => 'w-100 btn btn-primary'
        ]);

    }

    public function process(): self
    {
        if($this->isSubmitted()) {
            
            $post = array_map('trim', $_POST);

            if($this->isTrusted()) {

                $approved = 
                    $this->validateEmail($post['email'])
                    && $this->validatePassword($post['password'])
                    && $this->validateConfirmPassword($post['password'], $post['confirmPassword']);

                if($approved) {

                    $this->persist($post);

                } else {
                    $post['password'] = $post['confirmPassword'] = null;
                    $this->populate($post);
                }

            }

        };

        return $this;
    }

    protected function persist($post) {
        unset($post['confirmPassword']);
        unset($post['agreement']);
        $post['email'] = strtolower($post['email']);
        $post['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
        $post['usercode'] = Core::keygen(6);
        $this->flush($post);
    }

    protected function flush($post) {
        $SQL = SQuery::insert(DB_PREFIX . "_users", $post);
        $result = Uss::instance()->mysqli->query($SQL);
        if($result) {
            $location = $this->getRouteUrl('pages:index');
            header("location: {$location}");
            exit;
        }
    }

    private function validateEmail(string $email) {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if(!$email) {
            $this->setReport('email', "Invalid email address");
        };
        return $email;
    }

    private function validatePassword(string $password) {
        if(strlen($password) < 6) {
            $this->setReport('password', "Password should be at least 6 characters");
            return;
        };
        return $password;
    }

    private function validateConfirmPassword(string $password, string $confirmPassword) {
        if($password !== $confirmPassword) {
            $this->setReport('confirmPassword', "Confirm password does not match");
            return;
        };
        return $confirmPassword;
    }

}