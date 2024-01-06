<?php

namespace Module\Dashboard\Foundation\User\Form;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Bundle\Immutable\RoleImmutable;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Module\Dashboard\Foundation\User\Form\Service\EmailResolver;
use Module\Dashboard\Foundation\User\Form\Service\Validator;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Kernel\Uss;

class RegisterForm extends AbstractUserAccountForm
{
    public function buildForm(): void
    {
        $this->populateWithFakeUserInfo([
            'user[password]' => '&z25#W12_',
            'user[confirmPassword]' => '&z25#W12_'
        ]);

        !!Uss::instance()->options->get('user:collect-username') ? $this->createUsernameField() : null;
        $this->createEmailField();
        $this->createPasswordField();
        $this->createPasswordField(true);
        $this->createAgreementCheckboxField();
        $this->createNonceField();
        $this->createSubmitButton();
        $this->hideLabels();
    }

    public function validateResource(array $resource): array|bool|null
    {
        $resource = $this->validateNonce($resource);
        if($resource) {
            $user = $resource['user'];
            $validator = new Validator();
            $valid = (
                $validator->validateUsername($this->collection, $user['username'] ?? null) &&
                $validator->validateEmail($this->collection, $user['email'] ?? null) &&
                $validator->validatePassword($this->collection, $user['password'] ?? null) &&
                $validator->validateConfirmationPassword(
                    $this->collection,
                    $user['password'],
                    $user['confirmPassword'] ?? null
                )
            );
            if($valid && array_key_exists('confirmPassword', $user)) {
                unset($user['confirmPassword']);
            }
            return $valid ? $user : false;
        };
        return null;
    }

    public function persistResource(array $resource): mixed
    {
        $uss = Uss::instance();
        $user = new User();
        $resource = $uss->sanitize($resource, true);
        $defaultRole = $uss->options->get('user:default-role') ?? RoleImmutable::ROLE_USER;

        $user->setUsername($resource['username'] ?? null);
        $user->setEmail($resource['email']);
        $user->setPassword($resource['password'], true);
        $user->setUsercode(Uss::instance()->keygen(7));
        $user->setParent($resource['parent'] ?? null);
        $user->persist();
        $user->roles->add($defaultRole);

        return $user;
    }

    protected function resolveSubmission(mixed $user): void
    {
        $uss = Uss::instance(); // Pairs
        $dashboard = UserDashboard::instance();

        $summary = $this->getProperty('error:summary');

        $message = [
            'title' => 'Registration Failed',
            'message' =>
                $this->getProperty('error:message') ??
                'Sorry! We encountered an issue during the registration process.',
        ];

        $successRedirect =
            $this->getProperty('success:redirect') ??
            $dashboard->getDocument('index')->getUrl();

        if($user->isAvailable()) {

            $summary =
                $this->getProperty('success:summary') ??
                sprintf('You can now <a href="%s">login</a> with your credentials.', $successRedirect);

            $message = [
                'title' => "Registration Successful",
                'message' =>
                    $this->getProperty('success:message') ??
                    "Your account has been created successfully."
            ];

            if($uss->options->get('user:confirm-email')) {
                $emailResolver = new EmailResolver($this->getProperties());
                $emailSent = $emailResolver->sendConfirmationEmail($user);
                $summary = $emailResolver->getConfirmationEmailSummary($emailSent);
            }

            if(!empty($summary)) {
                $message['message'] .= '<div class="alert alert-secondary mt-2 small mb-0">' . $summary . '</div>';
            }
        }

        $modal = new Modal();
        $modal->setMessage($message['message']);
        $modal->setTitle($message['title']);

        Flash::instance()->addModal("registeration", $modal);

        if($user->isAvailable()) {
            header("location: {$successRedirect}");
            exit;
        }

    }
}
