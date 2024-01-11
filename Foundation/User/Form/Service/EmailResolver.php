<?php

namespace Module\Dashboard\Foundation\User\Form\Service;

use DateTime;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractEmailResolver;
use Module\Dashboard\Foundation\User\UserDashboard;

class EmailResolver extends AbstractEmailResolver
{
    /**
     * Registration page email
     *
     * Send a confirmation email to user at the point of registration
     */
    public function sendConfirmationEmail(User $user): bool
    {
        $indexDocument = UserDashboard::instance()->getDocument('index');

        $this->properties['email:subject'] ??= 'Your Confirmation Link';
        $this->properties['email:template'] ??= '@Foundation/User/Template/security/mails/register.email.twig';
        $this->properties['email:template.context'] ??= $this->getSystemContext($user);

        $this->properties['email:template.context'] += [
            'confirmation_link' => $this->generateEmailLink($user, [
                'metaKey' => 'verify-email:code',
                'urlKey' => 'verify-email',
                'destination' => $indexDocument->getUrl(),
            ])
        ];

        return $this->emailProcessor($user);
    }

    /**
     * Login page email verification
     *
     * Verity the email when user clicks the confirmation link sent at the point registration
     */
    public function verifyAccountEmail(): void
    {
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            $encoding = $_GET['verify-email'] ?? null;
            if(!empty($encoding)) {
                $data = base64_decode($encoding, true);
                if($data !== false) {
                    $context = explode(":", $data);
                    if(count($context) == 2) {
                        $this->verifyEmailContext(
                            $context[0] ?? null,
                            $context[1] ?? null,
                            'verify-email:code'
                        );
                    };
                }
            }
        }
    }

    /**
     * Password reset email
     *
     * Send a reset password verification link when user submit their email for changing password
     */
    public function sendRecoveryEmail(User $user): bool
    {
        $recoveryDocument = UserDashboard::instance()->getDocument('recovery');

        $this->properties['email:subject'] ??= 'Reset Your Password';
        $this->properties['email:template'] ??= '@Foundation/User/Template/security/mails/recovery.email.twig';
        $this->properties['email:template.context'] ??= $this->getSystemContext($user);

        $this->properties['email:template.context'] += [
            'reset_link' => $this->generateEmailLink($user, [
                'metaKey' => 'reset-password:code',
                'urlKey' => 'link',
                'destination' => $recoveryDocument->getUrl(),
            ])
        ];

        return $this->emailProcessor($user);
    }

    /**
     * Password reset email verification
     *
     * Verify the password link sent to user email to enable them change their password externally
     */
    public function verifyRecoveryEmail(): ?string
    {
        if(!empty($_GET['link'])) {
            $link = base64_decode($_GET['link']);
            if($link) {
                $message = "Invalid password reset link";
                $data = explode(":", $link);
                if(count($data) === 2) {
                    $message = "Ineffectve password reset link";
                    $user = new User($data[0] ?? null);
                    if($user) {
                        $message = "Incorrect password reset link";
                        $valid = $user->meta->get("reset-password:code") == $data[1];
                        if($valid) {
                            $message = "Password reset link expired";
                            $timestamp = $user->meta->get("reset-password:code", true);
                            $dateTime = (new DateTime())->setTimestamp($timestamp)->diff(new DateTime());
                            if($dateTime->h < 1) {
                                return $user->getEmail();
                            }
                            $user->meta->remove('reset-password:code');
                        }
                    }
                }
            }

            if($_SERVER['REQUEST_METHOD'] === 'GET') {
                $toast = new Toast();
                $toast->setMessage($message);
                $toast->setBackground(Toast::BG_DANGER);
                Flash::instance()->addToast("reset-link", $toast);
            }
        }

        return null;
    }

    /**
     * Profile update email
     *
     * Send a confirmation email to user at the point of updating profile
     */
    public function sendProfileUpdateEmail(User $user, string $newEmail): bool
    {
        $userProfileDocument = UserDashboard::instance()->getDocument('userProfile');

        $this->properties['email:subject'] ??= 'Confirm Your new email';
        $this->properties['email:template'] ??= '@Foundation/User/Template/profile/mails/reconfirm.email.twig';
        $this->properties['email:template.context'] ??= $this->getSystemContext($user);

        $this->properties['email:template.context'] += [
            'confirmation_link' => $this->generateEmailLink($user, [
                'metaKey' => 'profile-email:code',
                'urlKey' => 'verify',
                'destination' => $userProfileDocument->getUrl(),
                'data' => [
                    'email' => $newEmail
                ]
            ])
        ];

        return $this->emailProcessor($newEmail);
    }

    public function verifyProfileUpdateEmail(): void
    {
        $profileEmailKey = 'profile-email:code';
        if(!empty($_GET['verify'])) {
            $decoding = base64_decode($_GET['verify']);
            if($decoding && $data = explode(":", $decoding)) {
                if(count($data) === 2) {
                    $toast = new Toast();
                    $toast->setBackground(Toast::BG_DANGER);
                    $message = "Invalid verfication code";
                    $user = new User($data[0]);
                    if($user->isAvailable()) {
                        $context  = $user->meta->get($profileEmailKey);
                        if(!$context) {
                            return;
                        }
                        $message = "Verification code expired";
                        if($context['code'] === $data[1]) {
                            $message = "Email update failed";
                            $user->setEmail($context['data']['email']);
                            if($user->persist()) {
                                $user->meta->remove($profileEmailKey);
                                $message = "Email address updated!";
                                $toast->setBackground(Toast::BG_SUCCESS);
                            }
                        };
                    };
                    $toast->setMessage($message);
                    Flash::instance()->addToast("profile-email", $toast);
                }
            }
        };
    }
}
