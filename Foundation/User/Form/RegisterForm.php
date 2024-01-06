<?php

namespace Module\Dashboard\Foundation\User\Form;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Bundle\Mailer\Mailer;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
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
            $valid = (
                $this->validateUsername($user['username'] ?? null) &&
                $this->validateEmail($user['email'] ?? null) &&
                $this->validatePassword(
                    $user['password'] ?? null,
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
        $user = new User();
        $user->setUsername($resource['username'] ?? null);
        $user->setEmail($resource['email']);
        $user->setPassword($resource['password'], true);
        $user->setUsercode(Uss::instance()->keygen(7));
        $user->setParent($resource['parent'] ?? null);
        $user->persist();
        return $user;
    }

    protected function resolveSubmission(mixed $user): void
    {
        $uss = Uss::instance(); // Pairs
        $dashboard = UserDashboard::instance();

        $summary = $this->getProperty('registration-error:summary');

        $message = [
            'title' => 'Registration Failed',
            'message' => 
                $this->getProperty('registration-error:message') ?? 
                'Sorry! We encountered an issue during the registration process.',
        ];

        $successRedirect = 
            $this->getProperty('registration-success:redirect') ?? 
            $dashboard->getDocument('index')->getUrl();

        if($user->isAvailable()) {

            $summary = 
                $this->getProperty('registration-success:summary') ?? 
                sprintf('You can now <a href="%s">login</a> with your credentials.', $successRedirect);

            $message = [
                'title' => "Registration Successful",
                'message' => 
                    $this->getProperty('registration-success:message') ?? 
                    "Your account has been created successfully."
            ];

            if($uss->options->get('user:confirm-email')) {

                $registrationEmailSubject =
                    $this->getProperty('registration-email:subject') ??
                    'Your Confirmation Link';

                $registrationEmailTemplate =
                    $this->getProperty('registration-email:template') ??
                    '@Foundation/User/Template/security/mails/register.email.twig';

                $registrationEmailTemplateContext =
                    $this->getProperty('registration-email:template.context') ??
                    [
                        'privacy_policy_url' => $this->getProperty('privacyPolicyUrl') ?: '#',
                        'client_name' => $user->getUsername(),
                        'confirmation_link' => $this->getConfirmationLink(
                            $user,
                            $dashboard->getDocument('index')->getUrl()
                        ),
                    ];

                $mailer = new Mailer();
                $mailer->useMailHogTesting();

                $mailer->addAddress($user->getEmail());
                $mailer->setSubject($registrationEmailSubject);
                $mailer->setTemplate($registrationEmailTemplate);
                $mailer->setContext($registrationEmailTemplateContext);

                $summary = 
                    $this->getProperty('registration-email-error:summary') ??
                    'We could not sent a confirmation email to you <br> 
                    Please contact the support team to resolve your account';
                
                if($mailer->sendMail()) {
                    $summary = 
                        $this->getProperty('registration-email-success:summary') ??
                        'Please check your email to confirm the link we sent';
                }
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

    protected function validateUsername(?string $username): bool
    {
        if($username !== null) {
            // Username validation logic here
            if(!preg_match("/^\w{3,}$/i", trim($username))) {
                $usernameInfo = "Username should be at least 3 characters containing only letter, numbers and underscore";
                $usernameContext = $this->collection->getField('user[username]')?->getElementContext();
                if($usernameContext) {
                    $usernameContext->validation->setValue('* Invalid Username');
                    $usernameContext->info->setValue($usernameInfo);
                }
                return false;
            }
        }
        return true;
    }

    protected function validateEmail(?string $email): bool
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if(!$email) {
            $emailContext = $this->collection->getField('user[email]')?->getElementContext();
            $emailContext ? $emailContext->validation->setValue('* Invalid registration email') : null;
            return false;
        }
        return true;
    }

    protected function validatePassword(?string $password, ?string $confirmPassword): bool
    {
        $passwordResolver = $this->getPasswordResolver($password);

        $information = sprintf(
            "<div class='mb-1'>Your password should contain: %s</div>",
            Uss::instance()->implodeReadable($passwordResolver['requirements'])
        );

        if($passwordResolver['strength'] < 5) {
            $passwordContext = $this->collection->getField("user[password]")?->getElementContext();
            if($passwordContext) {
                $passwordContext->info->setValue($information);
                $passwordContext->validation->setValue($passwordResolver['errorMessage']);
            }
            return false;
        };

        if($confirmPassword !== null && $password !== $confirmPassword) {
            $passwordContext = $this->collection->getField("user[confirmPassword]")?->getElementContext();
            $passwordContext ? $passwordContext->validation->setValue("Password does not match") : null;
            return false;
        }

        return true;
    }

    protected function getConfirmationLink(User $user, string $destination): string
    {
        $confirmationCode = Uss::instance()->keygen(20);
        $user->meta->set('verify-email:code', $confirmationCode);
        $emailCode = base64_encode($user->getId() . ":" . $confirmationCode);
        return $this->addUrlParameter($destination, 'verify-email', $emailCode);
    }

    protected function addUrlParameter(string $url, string $name, string $value): string
    {
        $parsed_url = parse_url($url);
        $hasQuery = isset($parsed_url['query']);
        $url .= $hasQuery ? (($parsed_url['query'] === '') ? '' : '&') : '?';
        return $url . "$name=$value";
    }
}
