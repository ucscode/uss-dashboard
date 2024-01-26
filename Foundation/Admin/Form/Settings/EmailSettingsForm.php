<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings;

use Module\Dashboard\Foundation\Admin\Form\Settings\Abstract\AbstractEmailSettingsForm;
use Uss\Component\Kernel\Uss;

class EmailSettingsForm extends AbstractEmailSettingsForm
{
    public function buildForm(): void
    {
        $this->uss = Uss::instance();
        $this->createEmailCollectionFields();
        $this->createSMTPCollectionFields();
        $this->createOtherFields();
    }

    protected function validateResource(array $filteredResource): ?array
    {
        return null;
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {
        
    }
}