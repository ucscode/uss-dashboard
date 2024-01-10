<?php

namespace Module\Dashboard\Foundation\User\Form\Abstract;

use Uss\Component\Kernel\Uss;
use Module\Dashboard\Bundle\Mailer\Mailer;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\User\User;

abstract class AbstractEmailResolver
{
    protected bool $isLocalhost;

    public function __construct(protected array $properties)
    {
        $this->isLocalhost = in_array($_SERVER['SERVER_NAME'], [
            'localhost',
            '127.0.0.1',
            '::1'
        ]);
    }

    protected function emailProcessor(User $user): bool
    {
        $mailer = new Mailer();

        $this->isLocalhost ? $mailer->useMailHogTesting() : null;
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
            'We could not sent a confirmation email to you! <br> 
            Please request for a new confirmation email to access your account';

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

    public function generateEmailLink(User $user, array $context): string
    {
        $context['data'] ??= null;
        $confirmationCode = Uss::instance()->keygen(20);
        $metaValue = is_null($context['data']) ? $confirmationCode : [
            "code" => $confirmationCode,
            "data" => $context['data'],
        ];
        $user->meta->set($context['metaKey'], $metaValue);
        $emailCode = base64_encode($user->getId() . ":" . $confirmationCode);
        return $this->addUrlParameter($context['destination'], $context['urlKey'], $emailCode);
    }


    protected function verifyEmailContext(?int $userid, ?string $inputCode, string $metaKey): void
    {
        $toast = new Toast();
        $toast->setBackground(Toast::BG_SECONDARY);
        $toast->setMessage("Invalid Confirmation Link");

        if($userid && $inputCode) {
            $user = new User($userid);

            if($user->isAvailable()) {
                $storedCode = $user->meta->get($metaKey);
                $toast->setMessage("Email Confirmation Failed!");

                if($storedCode === null) {
                    return;
                }

                if($storedCode === $inputCode) {
                    $user->meta->remove($metaKey);
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

    protected function addUrlParameter(string $url, string $name, string $value): string
    {
        $parsed_url = parse_url($url);
        $hasQuery = isset($parsed_url['query']);
        $url .= $hasQuery ? (($parsed_url['query'] === '') ? '' : '&') : '?';
        return $url . "$name=$value";
    }
}
