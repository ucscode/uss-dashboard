<?php

namespace Module\Dashboard\Foundation\User\Form\Abstract;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\User\User;

abstract class AbstractEmailVerification extends AbstractUserAccountForm
{
    protected function verifyEmail(): void
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

    protected function verifyEmailContext(?int $userid, ?string $inputCode): void
    {
        $toast = new Toast();
        $toast->setBackground(Toast::BG_SECONDARY);
        $toast->setMessage("Invalid Confirmation Link");

        if($userid && $inputCode) 
        {
            $user = new User($userid);

            if($user->isAvailable()) 
            {
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
}