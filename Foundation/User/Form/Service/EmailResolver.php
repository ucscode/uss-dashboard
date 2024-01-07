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
     * @method sendConfirmationEmail:REGISTRATION
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
     * @method verifyAccountEmail:LOGIN
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
                        $this->verifyEmailContext($context[0] ?? null, $context[1] ?? null);
                    };
                }
            }
        }
    }

    /**
     * @method sendRecoveryEmail:RESET-PASSWORD
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
     * @method verifyRecoveryEmail:RESET-PASSWORD
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
}
