<?php

namespace Module\Dashboard\Foundation\User\Form\Abstract;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;

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

    protected function verifyEmailContext(?int $userid, ?string $code): void
    {
        if(!$userid || $code) {
            $toast = new Toast();
            Flash::instance()->addToast("my-toast", $toast);
        }
    }
}