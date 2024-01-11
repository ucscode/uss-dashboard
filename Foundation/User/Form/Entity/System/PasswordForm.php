<?php

namespace Module\Dashboard\Foundation\User\Form\Entity\System;

use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Ucscode\UssForm\Field\Field;

class PasswordForm extends AbstractUserAccountForm
{
    protected function buildForm(): void
    {
        $this->createCustomField([
            'nodeType' => Field::TYPE_PASSWORD,
            'name' => 'user[current_password]',
            'label' => 'Current Password',
        ]);
        $this->createPasswordField(false, 'New Password');
        $this->createPasswordField(true);
        $this->createSubmitButton('Save Changes');
        $this->hideLabels();
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