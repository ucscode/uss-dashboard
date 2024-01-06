<?php

namespace Module\Dashboard\Foundation\User\Form\Partition;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Service\Validator;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Foundation\User\UserDashboard;

class RecoveryFormAdvance extends AbstractRecoveryPartition
{
    public function buildForm(): void
    {
        $this->recoveryForm
            ->collection->getElementContext()
                ->title->setValue("Enter your new Password");

        $this->recoveryForm->createPasswordField();
        $this->recoveryForm->createPasswordField(true);
        $this->recoveryForm->createHiddenField('user[email]', $this->authorizedEmail);
        $this->recoveryForm->hideLabels();
    }

    public function validateResource(array $filteredResource): ?array
    {
        $validator = new Validator();

        $passwordValid = $validator->validatePassword(
            $this->recoveryForm->collection, 
            $filteredResource['password']
        );

        $confirmPasswordValid = $validator->validateConfirmationPassword(
            $this->recoveryForm->collection, 
            $filteredResource['password'], 
            $filteredResource['confirmPassword']
        );

        return $passwordValid && $confirmPasswordValid ? $filteredResource : null;
    }

    public function persistResource(?array $validatedResource): ?User
    {
        $user = null;

        if($validatedResource !== null) {

            $modal = new Modal();
            $modal->setMessage("Password reset process could not be completed");
            $modal->setTitle("Request Failed");

            $user = new User();
            $user->allocate("email", $validatedResource['email']);
            $user->setPassword($validatedResource['password'], true);

            if($user->persist()) {
                $modal->setTitle("Password Reset Success");
                $modal->setMessage("Your password reset was successful. <br> Your account is now updated with the new password.");
            }

            Flash::instance()->addModal("password-reset", $modal);
        }

        return $user;
    }

    public function resolveSubmission(?User $user): void
    {
        if($user !== null) {
            $indexDocument = UserDashboard::instance()->getDocument("index");
            header("location: " . $indexDocument->getUrl());
            exit;
        };
    }
}