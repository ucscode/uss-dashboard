<?php

namespace Module\Dashboard\Foundation\User\Form;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Modal\Button;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Uss\Component\Kernel\Uss;

class RegisterForm extends AbstractUserAccountForm
{
    public function buildForm(): void
    {
        $this->populateWithFakeUserInfo([
            'user[password]' => '&z25#W12_',
            'user[confirmPassword]' => '&z25#W12_'
        ]);
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
            $valid = (
                $this->validateUsername($user['username'] ?? null) &&
                $this->validateEmail($user['email'] ?? null) &&
                $this->validatePassword(
                    $user['password'] ?? null,
                    $user['confirmPassword'] ?? null
                )
            );
            if($valid && array_key_exists('confirmPassword', $user)) {
                unset($user['confirmPassword']);
            }
            return $valid ? $user : false;
        };
        return null;
    }

    public function persistResource(array $resource): mixed
    {
        $user = new User();
        $user->setUsername($resource['username'] ?? null);
        $user->setEmail($resource['email']);
        $user->setPassword($resource['password']);
        $user->setUsercode(Uss::instance()->keygen());
        $user->setParent($resource['parent'] ?? null);
        return $user;
    }

    protected function resolveSubmission(mixed $response): void
    {
        var_dump($response);
        return;
        $modal = new Modal();
        $modal->setMessage("
            <div>
                # This is an md file.
                We're not here to laugh - says \"Jonny\":
                - Use `window.alert()` function to shout out loud
            </div>
        ");
        $modal->setTitle("Your coding");

        $button = new Button();

        $button->setLabel("Confirm");
        $button->setClassName("btn btn-success");
        $button->setCallback('window.sample');

        $modal->addButton("my-button", $button);
        $modal->setCustomCallback("onEscape", "window.alert");

        Flash::instance()->addModal("new", $modal);
        
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
            $emailContext ? $emailContext->validation->setValue('* Invalid registration email') : null;
            return false;
        }
        return true;
    }

    protected function validatePassword(?string $password, ?string $confirmPassword): bool
    {
        $passwordResolver = $this->getPasswordResolver($password);

        $information = sprintf(
            "<div class='mb-1'>Your password should contain: %s</div>",
            Uss::instance()->implodeReadable($passwordResolver['requirements'])
        );

        if($passwordResolver['strength'] < 5) {
            $passwordContext = $this->collection->getField("user[password]")?->getElementContext();
            if($passwordContext) {
                $passwordContext->info->setValue($information);
                $passwordContext->validation->setValue($passwordResolver['errorMessage']);
            }
            return false;
        };

        if($confirmPassword !== null && $password !== $confirmPassword) {
            $passwordContext = $this->collection->getField("user[confirmPassword]")->getElementContext();
            $passwordContext ? $passwordContext->validation->setValue("Password does not match") : null;
            return false;
        }

        return true;
    }


}
