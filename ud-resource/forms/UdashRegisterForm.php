<?php

use Ucscode\UssForm\UssForm;
use Ucscode\Packages\SQuery;

class UdashRegisterForm extends AbstractUdashForm {

    protected function buildForm() {

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

    /**
     * Process the form data
     *
     * @return self The registration form object
     */ 
    public function process(): self
    {
        if($this->isSubmitted()) { 

            if($this->isTrusted()) { 

                $post = $this->getApprovedData();

                if(is_array($post)) {

                    $this->saveToDatabase($post);

                } else {

                    $this->populate($_POST);

                }; 

            }; // !Trust

        }; // !Submit

        return $this;
    }

    protected function getApprovedData(): array|bool {

        $post = $_POST['user'] ?? [];
        array_walk_recursive($post, 'trim');

        $approved = 
            $this->validateEmail($post['email'])
            && $this->validatePassword($post['password'], $post['confirmPassword']);

        if(!$approved) {
            return false;
        };

        unset($post['confirmPassword']);
        $post['email'] = strtolower($post['email']);

        return $post;

    }

    protected function saveToDatabase(array $post) {
    
        $post['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
        $post['usercode'] = Core::keygen(6);
        
        $SQL = SQuery::insert(DB_PREFIX . "users", $post);
        $result = Uss::instance()->mysqli->query($SQL);

        if($result) {
            $location = $this->redirectUrl ?: $this->getRouteUrl('pages:index');
            header("location: {$location}");
            exit;
        };

    }

    /**
     * [VALIDATION] METHODS
     *
     * @ignore
     */
    protected function validateEmail(string $email) {
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

    protected function validatePassword(string $password, string $confirmPassword) {
        if(strlen($password) < 6) {
            $this->setReport('user[password]', "Password should be at least 6 characters");
            return false;
        } else if($password !== $confirmPassword) {
            $this->setReport('user[confirmPassword]', "Confirm password does not match");
            return false;
        };
        return true;
    }

}