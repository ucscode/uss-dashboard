<?php

namespace Module\Dashboard\Foundation\User\Form;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Module\Dashboard\Foundation\User\Form\Service\EmailResolver;
use Module\Dashboard\Foundation\User\Form\Service\Validator;

class RecoveryForm extends AbstractUserAccountForm
{
    public function buildForm(): void
    {
        $this->createEmailField('Please enter your email address')
            ->getElementContext()->widget
                ->setAttribute('placeholder', 'email');
        $this->createNonceField();
        $this->createSubmitButton();
    }

    public function validateResource(array $resource): array|bool|null
    {
        if($resource = $this->validateNonce($resource)) {
            $user = $resource["user"];
            $validator = new Validator();
            $emailIsValid = $validator->validateEmail($this->collection, $user['email']);
            return $emailIsValid ? $user : false;
        }
        return false;
    }

    public function persistResource(array $resource): mixed
    {
        if($resource) {
            $user = (new User())->allocate('email', $resource['email']);
            if($user->isAvailable()) {
                $emailResolver = new EmailResolver($this->getProperties());
                if($emailResolver->sendRecoveryEmail($user)) {
                    return $user;
                }
            }
        }
        return false;
    }

    protected function resolveSubmission(mixed $response): void
    {
        $toast = new Toast();
        $toast->setMessage("The email account was not found");
        $toast->setBackground(Toast::BG_DANGER);

        $user = $response;

        if($user) {

            return;
        }

        Flash::instance()->addToast("email-recovery", $toast);
    }
}
