<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Foundation\Admin\Form\Settings\Abstract\AbstractUsersSettingsForm;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

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
        $validNonce = Uss::instance()->nonce($_SESSION[UssImmutable::APP_SESSION_KEY], $filteredResource['nonce']);
        if($validNonce) {
            return $filteredResource['user'];
        }
        return null;
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        if($validatedResource) {
            $persisted = [];
            foreach($validatedResource as $key => $value) {
                $name = sprintf("user:%s", $key);
                $persisted[] = $this->uss->options->set($name, $value);
            };
            if(!in_array(false, $persisted, true)) {
                $toast = (new Toast())
                    ->setBackground(Toast::BG_SUCCESS)
                    ->setMessage("User settings updated");
                Flash::instance()->addToast($toast);
                return $validatedResource;
            }
        }
        $toast = (new Toast())
            ->setBackground(Toast::BG_DANGER)
            ->setMessage("User settings not updated");
        Flash::instance()->addToast($toast);
        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {
        $this->replaceHistoryState(false);
    }
}