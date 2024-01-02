<?php

namespace Module\Dashboard\Foundation\User\Form;

use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;

class RegisterForm extends AbstractUserAccountForm
{
    public function buildForm(): void
    {
        $this->populateWithFakeUserInfo();
        //$this->createUsernameField();
        $this->createEmailField();
        $this->createPasswordField();
        $this->createPasswordField(true);
        $this->createAgreementCheckboxField();
        $this->createNonceField();
        $this->createSubmitButton();
        $this->hideLabels();
    }

    public function validateResource(array $resource): array|bool|null
    {
        $resource = $this->validateNonce($resource);
        if($resource) {
            $user = $resource['user'];
            return (
                $this->validateUsername($user['username'] ?? null) &&
                $this->validateEmail($user['email'] ?? null) &&
                $this->validatePassword(
                    $user['password'] ?? null,
                    $user['confirmPassword'] ?? null
                )
            ) ? $user : false;
        };
        return null;
    }

    public function persistResource(array $resource): bool
    {
        echo 2;
        return false;
    }

    protected function validateUsername(?string $username): bool
    {
        if($username !== null) {
            // Username validation logic here
        }
        return true;
    }

    protected function validateEmail(?string $email): bool
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if(!$email) {
            $emailContext = $this->collection->getField('user[email]')?->getElementContext();
            if($emailContext) {
                $emailContext->validation
                    ->setValue('* Invalid registration email', true);
            }
            return false;
        }
        return true;
    }

    protected function validatePassword(?string $password, ?string $confirmPassword): bool
    {
        $passwordResolver = $this->getPasswordResolver($password);
        //var_dump($passwordResolver);
        return false;
    }


}
