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

    public function validateResource(array $resource): ?array
    {
        if($resource = $this->validateNonce($resource)) {
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
        return false;
    }

    protected function validateEmail(?string $email): bool
    {
        return false;
    }

    protected function validatePassword(?string $password, ?string $confirmPassword): bool
    {
        return false;
    }
}
