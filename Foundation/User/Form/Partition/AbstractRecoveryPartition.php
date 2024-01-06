<?php

namespace Module\Dashboard\Foundation\User\Form\Partition;

use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\RecoveryForm;

abstract class AbstractRecoveryPartition
{
    abstract public function buildForm(): void;
    abstract public function validateResource(array $filteredResource): ?array;
    abstract public function persistResource(?array $validatedResource): ?User;
    abstract public function resolveSubmission(?User $user): void;

    public function __construct(protected RecoveryForm $recoveryForm, protected ?string $authorizedEmail)
    {
        //
    }
}