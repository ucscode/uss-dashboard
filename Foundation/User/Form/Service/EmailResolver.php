<?php

namespace Module\Dashboard\Foundation\User\Form\Service;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\User\User;
use Uss\Component\Kernel\Uss;
use Module\Dashboard\Bundle\Mailer\Mailer;
use Module\Dashboard\Foundation\User\UserDashboard;

class EmailResolver
{
    public function __construct(protected array $properties)
    {

    }

    public function sendConfirmationEmail(User $user): bool
    {
        $this->properties['email:subject'] ??= 'Your Confirmation Link';
        $this->properties['email:template'] ??= '@Foundation/User/Template/security/mails/register.email.twig';
        $this->properties['email:template.context'] ??= $this->getSystemContext($user);

        $this->properties['email:template.context'] += [
            'confirmation_link' => $this->getConfirmationLink(
                $user, UserDashboard::instance()->getDocument('index')->getUrl()
            ),
        ];
        
        return $this->emailProcessor($user);
    }

    public function getConfirmationEmailSummary(bool $confirmationEmailSent): string
    {
        $summary =
            $this->properties['registration-email-error:summary'] ??
            'We could not sent a confirmation email to you <br> 
        Please contact the support team to resolve your account';

        if($confirmationEmailSent) {
            $summary =
                $this->properties['registration-email-success:summary'] ??
                'Please check your email to confirm the link we sent';
        };

        return $summary;
    }

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

    public function sendRecoveryEmail(User $user): bool
    {
        $this->properties['email:subject'] ??= 'Reset Your Password';
        $this->properties['email:template'] ??= '@Foundation/User/Template/security/mails/recovery.email.twig';
        $this->properties['email:template.context'] ??= $this->getSystemContext($user);
        
        return $this->emailProcessor($user);
    }

    protected function emailProcessor(User $user): bool
    {
        $mailer = new Mailer();

        $mailer->useMailHogTesting();
        $mailer->addAddress($user->getEmail());
        $mailer->setSubject($this->properties['email:subject']);
        $mailer->setTemplate($this->properties['email:template']);
        $mailer->setContext($this->properties['email:template.context']);
        // echo $mailer->getTemplateOutput();
        // exit;
        return $mailer->sendMail();
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

    protected function addUrlParameter(string $url, string $name, string $value): string
    {
        $parsed_url = parse_url($url);
        $hasQuery = isset($parsed_url['query']);
        $url .= $hasQuery ? (($parsed_url['query'] === '') ? '' : '&') : '?';
        return $url . "$name=$value";
    }
}
