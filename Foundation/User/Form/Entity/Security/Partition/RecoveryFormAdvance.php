<?php

namespace Module\Dashboard\Foundation\User\Form\Entity\Security\Partition;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Service\Validator;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Foundation\User\UserDashboard;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractRecoveryPartition;

class RecoveryFormAdvance extends AbstractRecoveryPartition
{
    public function buildForm(): void
    {
        $this->recoveryForm
            ->collection->getElementContext()
                ->title->setValue("Enter your new Password");

        $this->recoveryForm->createPasswordField();
        $this->recoveryForm->createPasswordField(true);
        $this->recoveryForm->createHiddenField('user[email]', $this->verifiedEmail);
        $this->recoveryForm->hideLabels();
    }

    public function validateResource(array $filteredResource): ?array
    {
        $filteredResource = array_map('trim', $filteredResource);
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

            $user = (new User())
                ->allocate("email", strtolower($validatedResource['email']))
                ->setPassword($validatedResource['password'], true);

            $user = $user->isAvailable() ? $user : null;

            if($user && $user->persist()) {
                $user->meta->remove("reset-password:code");
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
            $indexDocument = $this->recoveryForm->getDashboardInterface()->getDocument("index");
            header("location: " . $indexDocument->getUrl());
            exit;
        };
    }
}
