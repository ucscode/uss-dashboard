<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings;

use Module\Dashboard\Foundation\Admin\Form\Settings\Abstract\AbstractSystemSettingsForm;
use Ucscode\UssForm\Collection\Collection;

class SystemSettingsForm extends AbstractSystemSettingsForm
{
    protected function buildForm(): void
    {
        $this->createPrimaryFields();
        $this->createAvatarFields();
    }

    protected function validateResource(array $filteredResource): ?array
    {
        return [];
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {
        
    }
}