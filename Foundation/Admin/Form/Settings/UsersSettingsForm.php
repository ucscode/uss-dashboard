<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings;

use Module\Dashboard\Foundation\Admin\Form\Settings\Abstract\AbstractUsersSettingsForm;

class UsersSettingsForm extends AbstractUsersSettingsForm
{
    public function buildForm(): void
    {
        $this->createSignupDisabledField();
        $this->createCollectUsernameField();
        $this->createConfirmEmailField();
        $this->createReadonlyEmailField();
        $this->createReconfirmEmailField();
        $this->createAccountAutoDeletionField();
        $this->createDefaultRoleField();
        $this->createNonceField();
        $this->createSubmitButton();
    }

    protected function validateResource(array $filteredResource): ?array
    {
        var_dump($filteredResource);
        return null;
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {
        $this->replaceHistoryState(false);
    }
}