<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Compact;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;

class CrudEditorForm extends AbstractDashboardForm
{
    public function isSubmitted(): bool
    {
        return false;
    }
    
    protected function buildForm(): void
    {
        
    }

    protected function validateResource(array $filteredResource): ?array
    {
        return [];
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {
        
    }
}