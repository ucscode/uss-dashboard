<?php

namespace Module\Dashboard\Foundation\User\Form;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Module\Dashboard\Foundation\User\Form\Partition\AbstractRecoveryPartition;
use Module\Dashboard\Foundation\User\Form\Partition\RecoveryFormAdvance;
use Module\Dashboard\Foundation\User\Form\Partition\RecoveryFormBasic;
use Module\Dashboard\Foundation\User\Form\Service\EmailResolver;

class RecoveryForm extends AbstractUserAccountForm
{
    protected ?string $authorizedEmail = null;
    protected AbstractRecoveryPartition $partition;

    public function buildForm(): void
    {
        $this->authorizedEmail = (new EmailResolver($this->getProperties()))->verifyRecoveryEmail();
        
        $this->populateWithFakeUserInfo();
        
        $this->partition = !$this->authorizedEmail ? 
            new RecoveryFormBasic($this, null) : 
            new RecoveryFormAdvance($this, $this->authorizedEmail);
        
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
