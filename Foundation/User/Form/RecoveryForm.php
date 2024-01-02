<?php

namespace Module\Dashboard\Foundation\User\Form;

use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;

class RecoveryForm extends AbstractUserAccountForm
{
    public function buildForm(): void
    {
        $this->createEmailField();
    }

    public function validateResource(array $resource): array|bool|null
    {
        return false;
    }

    public function persistResource(array $resource): bool
    {
        return false;
    }

    protected function resolveSubmission(mixed $response): void
    {
        
    }
}
