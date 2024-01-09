<?php

namespace Module\Dashboard\Foundation\User\Form\Entity\Security;

use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractRecoveryPartition;
use Module\Dashboard\Foundation\User\Form\Entity\Security\Partition\RecoveryFormAdvance;
use Module\Dashboard\Foundation\User\Form\Entity\Security\Partition\RecoveryFormBasic;
use Module\Dashboard\Foundation\User\Form\Service\EmailResolver;

class RecoveryForm extends AbstractUserAccountForm
{
    protected AbstractRecoveryPartition $partition;

    public function buildForm(): void
    {
        // $this->populateWithFakeUserInfo([
        //     'user[email]' => 'twill@funk.info',
        // ]);

        $verifiedEmail = (new EmailResolver([]))->verifyRecoveryEmail();

        $this->partition = !$verifiedEmail ?
            new RecoveryFormBasic($this, null) :
            new RecoveryFormAdvance($this, $verifiedEmail);

        $this->partition->buildForm();
        $this->createNonceField();
        $this->createSubmitButton();
    }

    public function validateResource(array $filteredResource): ?array
    {
        if($resource = $this->validateNonce($filteredResource)) {
            return $this->partition->validateResource($resource["user"]);
        }
        return null;
    }

    public function persistResource(?array $validatedResource): mixed
    {
        return $this->partition->persistResource($validatedResource);
    }

    protected function resolveSubmission(mixed $user): void
    {
        $this->partition->resolveSubmission($user);
    }
}
