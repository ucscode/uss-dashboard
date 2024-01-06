<?php

namespace Module\Dashboard\Foundation\User\Form\Abstract;

use Uss\Component\Kernel\Uss;
use Module\Dashboard\Bundle\Mailer\Mailer;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\User\User;

abstract class AbstractEmailResolver
{
    public function __construct(protected array $properties)
    {

    }

    protected function emailProcessor(User $user): bool
    {
        $mailer = new Mailer();

        $mailer->useMailHogTesting();
        $mailer->addAddress($user->getEmail());
        $mailer->setSubject($this->properties['email:subject']);
        $mailer->setTemplate($this->properties['email:template']);
        $mailer->setContext($this->properties['email:template.context']);

        return $mailer->sendMail();
    }

    public function getConfirmationEmailSummary(bool $confirmationEmailSent): string
    {
        $summary =
            $this->properties['email:summary.error'] ??
            'We could not sent a confirmation email to you <br> 
        Please contact the support team to resolve your account';

        if($confirmationEmailSent) {
            $summary =
                $this->properties['email:summary.success'] ??
                'Please check your email to confirm the link we sent';
        };

        return $summary;
    }

    public function getRecoveryEmailSummary(bool $passwordEmailSent): string
    {
        $summary =
            $this->properties['email:summary.error'] ??
            'The reset password email could not be delivered <br>
            Contact the support if the problem persist';

        if($passwordEmailSent) {
            $summary =
                $this->properties['email:summary.success'] ??
                'Please follow the password reset link we sent to your email';
        };

        return $summary;
    }


    protected function verifyEmailContext(?int $userid, ?string $inputCode): void
    {
        $toast = new Toast();
        $toast->setBackground(Toast::BG_SECONDARY);
        $toast->setMessage("Invalid Confirmation Link");

        if($userid && $inputCode) {
            $user = new User($userid);

            if($user->isAvailable()) {
                $storedCode = $user->meta->get('verify-email:code');
                $toast->setMessage("Email Confirmation Failed!");

                if($storedCode === null) {
                    return;
                }

                if($storedCode === $inputCode) {
                    $user->meta->remove('verify-email:code');
                    $toast->setBackground(Toast::BG_SUCCESS);
                    $toast->setMessage("Your email has been confirmed");
                }
            }
        }

        Flash::instance()->addToast("verify-email", $toast);
    }

    protected function getSystemContext(User $user): array
    {
        return [
            'privacy_policy_url' => $this->properties['privacyPolicyUrl'] ?? '#',
            'client_name' => $user->getUsername(),
        ];
    }

    protected function getConfirmationLink(User $user, string $destination): string
    {
        $confirmationCode = Uss::instance()->keygen(20);
        $user->meta->set('verify-email:code', $confirmationCode);
        $emailCode = base64_encode($user->getId() . ":" . $confirmationCode);
        return $this->addUrlParameter($destination, 'verify-email', $emailCode);
    }

    protected function getResetPasswordLink(User $user, string $destination): string
    {
        $confirmationCode = Uss::instance()->keygen(20);
        $user->meta->set('reset-password:code', $confirmationCode);
        $emailCode = base64_encode($user->getId() . ":" . $confirmationCode);
        return $this->addUrlParameter($destination, "link", $emailCode);
    }

    protected function addUrlParameter(string $url, string $name, string $value): string
    {
        $parsed_url = parse_url($url);
        $hasQuery = isset($parsed_url['query']);
        $url .= $hasQuery ? (($parsed_url['query'] === '') ? '' : '&') : '?';
        return $url . "$name=$value";
    }

    protected function verifyRecoveryContext(User $user, ?string $inputCode): bool
    {
        var_dump($user, $inputCode);
        return false;
    }
}
