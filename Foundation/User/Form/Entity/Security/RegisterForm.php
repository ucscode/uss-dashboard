<?php

namespace Module\Dashboard\Foundation\User\Form\Entity\Security;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\Immutable\RoleImmutable;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Module\Dashboard\Foundation\User\Form\Service\EmailResolver;
use Module\Dashboard\Foundation\User\Form\Service\Validator;
use Module\Dashboard\Foundation\User\UserDashboard;
use Ucscode\SQuery\Condition;
use Ucscode\SQuery\SQuery;
use Uss\Component\Kernel\Uss;

class RegisterForm extends AbstractUserAccountForm
{
    public function buildForm(): void
    {
        // $this->populateWithFakeUserInfo([
        //     'user[password]' => '5lPO$24rcC5Q',
        //     'user[confirmPassword]' => '5lPO$24rcC5Q'
        // ]);

        !!Uss::instance()->options->get('user:collect-username') ? $this->createUsernameField() : null;
        $this->createEmailField();
        $this->createPasswordField();
        $this->createPasswordField(true);
        $this->createAgreementCheckboxField();
        $this->createNonceField();
        $this->createSubmitButton();
        $this->hideLabels();
    }

    public function validateResource(array $filteredResource): ?array
    {
        $resource = $this->validateNonce($filteredResource);
        echo 1;
        if($resource) {

            $user = $resource['user'];

            array_walk($user, function (&$value, $key) {
                $value = trim($value);
                $value = (in_array(strtolower($key), ['username', 'email'])) ? strtolower($value) : $value;
            });

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

            if($valid) {
                if(array_key_exists('confirmPassword', $user)) {
                    unset($user['confirmPassword']);
                }
                return
                    $this->isNotAvailable($user, 'username') &&
                    $this->isNotAvailable($user, 'email') ? $user : null;
            }

            return null;
        };
        return null;
    }

    public function persistResource(?array $validatedResource): mixed
    {
        if($validatedResource !== null) {

            $uss = Uss::instance();
            $user = new User();
            $resource = $uss->sanitize($validatedResource, true);
            $defaultUserRole = $uss->options->get('user:default-role') ?? RoleImmutable::ROLE_USER;

            $user->setUsername($resource['username'] ?? null);
            $user->setEmail($resource['email']);
            $user->setPassword($resource['password'], true);
            $user->setUsercode(Uss::instance()->keygen(7));
            $user->setParent($resource['parent'] ?? null);

            $user->persist();
            $user->roles->add($defaultUserRole);

            return $user;
        }
        return null;
    }

    protected function resolveSubmission(mixed $user): void
    {
        if($user !== null) {

            $uss = Uss::instance();
            $indexDocument = UserDashboard::instance()->getDocument('index');

            $summary = $this->getProperty('error:summary');

            $message = [
                'title' => 'Registration Failed',
                'message' =>
                    $this->getProperty('error:message') ??
                    'Sorry! We encountered an issue during the registration process.',
            ];

            $successRedirect =
                $this->getProperty('success:redirect') ??
                $indexDocument?->getUrl();

            if($user->isAvailable()) {

                $summary =
                    $this->getProperty('success:summary') ??
                    'You can now login with your credentials.';

                $message = [
                    'title' => "Registration Successful",
                    'message' =>
                        $this->getProperty('success:message') ??
                        "Your account has been created successfully."
                ];

                if($uss->options->get('user:confirm-email')) {
                    $resolver = new EmailResolver($this->getProperties());
                    $emailSent = $resolver->sendConfirmationEmail($user);
                    $summary = $resolver->getConfirmationEmailSummary($emailSent);
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

    protected function isNotAvailable(array $user, string $key): bool
    {
        if(!empty($user[$key])) {
            $condition = (new Condition())->add($key, $user[$key]);

            $squery = (new SQuery())
                ->select('id')
                ->from(UserInterface::USER_TABLE)
                ->where($condition);

            $result = Uss::instance()->mysqli->query($squery->build());

            if($result->num_rows) {
                $toast = new Toast();
                $toast->setMessage(sprintf("Error: The %s already exists", $key));
                $toast->setBackground(Toast::BG_DANGER);
                Flash::instance()->addToast("user-available", $toast);
                return false;
            }
        }
        return true;
    }
}
