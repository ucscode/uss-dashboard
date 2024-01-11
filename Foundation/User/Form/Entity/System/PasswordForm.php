<?php

namespace Module\Dashboard\Foundation\User\Form\Entity\System;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Module\Dashboard\Foundation\User\Form\Service\PasswordResolver;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Kernel\Uss;

class PasswordForm extends AbstractUserAccountForm
{
    protected ?User $user;

    protected function buildForm(): void
    {
        $this->user = (new User())->acquireFromSession();
        $this->createCustomField([
            'nodeType' => Field::TYPE_PASSWORD,
            'name' => 'user[currentPassword]',
            'label' => 'Current Password',
        ]);
        $this->createPasswordField(false, 'New Password');
        $this->createPasswordField(true);
        $this->createNonceField();
        $this->createSubmitButton('Update Password');
        $this->hideLabels();
    }

    protected function validateResource(array $filteredResource): ?array
    {
        if($resource = $this->validateNonce($filteredResource)) {
            $user = $resource['user'];
            return (
                $this->validateCurrentPassword($user['currentPassword']) &&
                $this->validateNewPassword($user['password']) &&
                $this->validateConfirmPassword($user['password'], $user['confirmPassword'])
            ) ? $user : null;
        };
        return null;
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        if($validatedResource) {
            $title = "Password update failed";
            $message = "Your account password could not be updated";
            $this->user->setPassword($validatedResource['password'], true);
            if($this->user->persist()) {
                $this->user->saveToSession();
                $title = "Password update successful";
                $message = "Your account password was successfully updated";
            }
            $modal = new Modal();
            $modal->setTitle($title);
            $modal->setMessage($message);
            Flash::instance()->addModal("profile-password", $modal);
            return true;
        }
        return false;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {
        // $this->setProperty('history.replaceState', false);
    }

    protected function validateCurrentPassword(string $password): bool
    {
        if(!$this->user->verifyPassword($password)) {
            $passwordField = $this->collection->getField("user[currentPassword]");
            $passwordField->getElementContext()->validation
                ->setValue("* The current password is incorrect");
            return false;
        }
        return true;
    }

    protected function validateNewPassword(string $password): bool
    {
        $passwordResolver = (new PasswordResolver())->resolve($password);
        $uss = Uss::instance();
        $passwordContext = $this->collection->getField("user[password]")->getElementContext();
        
        if($passwordResolver['strength'] < $passwordResolver['strengthLimit']) {
            $passwordContext->validation
                ->setValue('* ' . $passwordResolver['errorMessage']);
            $passwordContext->info
                ->setValue(
                    sprintf(
                        "<i class='bi bi-exclamation-circle'></i> Your password should contain at least %s",
                        $uss->implodeReadable($passwordResolver['requirements'])
                    )
                )
                ->addClass('text-secondary');
            return false;
        };

        if($this->user->verifyPassword($password)) {
            $passwordContext->validation
                ->setValue("* Current &amp; new password cannot be the same");
            return false;
        }

        return true;
    }

    protected function validateConfirmPassword(string $password, string $confirmPassword): bool
    {
        $passwordContext = $this->collection->getField("user[confirmPassword]")->getElementContext();
        if($password !== $confirmPassword) {
            $passwordContext->validation
                ->setValue("* Password does not match! Try again");
            return false;
        }
        return true;
    }
}