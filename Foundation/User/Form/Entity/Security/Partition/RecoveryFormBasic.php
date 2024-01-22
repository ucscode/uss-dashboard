<?php

namespace Module\Dashboard\Foundation\User\Form\Entity\Security\Partition;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Service\EmailResolver;
use Module\Dashboard\Foundation\User\Form\Service\Validator;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractRecoveryPartition;

class RecoveryFormBasic extends AbstractRecoveryPartition
{
    public function buildForm(): void
    {
        $this->recoveryForm
            ->createEmailField('Please enter your email address', 'email')
            ->getElementContext()->widget
            ->setAttribute('placeholder', 'email');
    }

    public function validateResource(array $filteredResource): ?array
    {
        $filteredResource = array_map('trim', $filteredResource);
        $validator = new Validator();

        $validEmail = $validator->validateEmail(
            $this->recoveryForm->collection,
            $filteredResource['email']
        );

        return $validEmail ? $filteredResource : null;
    }

    public function persistResource(?array $validatedResource): ?User
    {
        $user = null;

        if($validatedResource !== null) {
            $modal = new Modal();
            $modal->setTitle("Request Failed");
            $modal->setMessage("The email account was not found");

            $user = new User();
            $user->allocate('email', strtolower($validatedResource['email']));

            if($user->isAvailable()) {
                [$sent, $message] = $this->sendPasswordResetEmail($user);
                $modal->setMessage($message);
                $sent ? $modal->setTitle("Reset Password Sent") : null;
            }

            Flash::instance()->addModal($modal, "password-reset");
        }

        return $user;
    }

    public function resolveSubmission(?User $user): void
    {
        //
    }

    protected function sendPasswordResetEmail(User $user): array
    {
        $resolver = new EmailResolver(
            $this->recoveryForm->getProperties(), 
            $this->recoveryForm->getDashboardInterface()
        );
        $sent = $resolver->sendRecoveryEmail($user);
        return [$sent, $resolver->getRecoveryEmailSummary($sent)];
    }
}
