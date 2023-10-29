<?php

abstract class AbstractUserRecoveryForm extends AbstractDashboardForm
{
    public int $stage = 0;
    public bool $status = false;
    public $expiryHour = 14;

    protected User $user;

    protected function defineStage(): void
    {
        $base64 = $_GET['verify'] ?? null;
        if(!empty($base64)) {
            $json = json_decode(base64_decode($base64), true);
            if(!json_last_error()) {
                $userId = $json['userid'] ?? null;
                if($userId && is_numeric($userId)) {
                    $this->user = new User($userId);
                    $saved_time = $this->user->getUserMeta('user.recovery_code', true);
                    if($saved_time) {
                        if((time() - $saved_time) > $this->expiryHour * 60 * 60) {
                            (new Alert('Reset Link has expired'))
                                ->type('notification')
                                ->display('info');
                        } else {
                            $code = $json['code'] ?? null;
                            $saved_code = $this->user->getUserMeta('user.recovery_code');
                            if($code === $saved_code) {
                                $this->stage = 1;
                            }
                        }
                    }
                }
            } else {
                (new Alert('Invalid Reset Password Link'))
                    ->type('notification')
                    ->display('warning');
            }
        };
    }

    protected function sendRecoveryEmail(User $user): bool
    {
        $recoveryInput = $this->getRecoveryInput($user);

        if($recoveryInput['saved']) {
            $uss = Uss::instance();
            $template = '@mail/skyline/activation.html.twig';
            $url = $recoveryInput['url'] . "?verify=" . base64_encode(json_encode($recoveryInput['data']));

            $emailBody = $uss->render($template, [
                'company_icon' => $uss->options->get('company:icon'),
                'company_name' => $uss->options->get('company:name'),
                'company_headline' => $uss->options->get('company:headline'),
                'company_about' => $uss->options->get('company:description'),
                'company_email' => $uss->options->get('company:email'),
                'activation_link' => $url,
                'user_title' => $user->getUsername(),
                'expiry_hour' => $this->expiryHour
            ], true);

            try {
                $PHPMailer = (new DashboardFactory())->createPHPMailer(true);
                $PHPMailer->Subject = 'Account Activation';
                $PHPMailer->Body = $emailBody;
                $PHPMailer->addAddress($user->getEmail(), $user->getUsername());
                $PHPMailer->send();
            } catch(\Exception $e) {
                return false;
            }
        }

        return true;
    }

    protected function getRecoveryInput(User $user): array
    {
        $dashboard = UserDashboard::instance();
        $recoveryCode = md5(mt_rand());
        return [
            'url' => $dashboard->getArchiveUrl('recovery'),
            'data' => [
                'userid' => $user->getId(),
                'code' => $recoveryCode
            ],
            'saved' => $user->setUserMeta('user.recovery_code', $recoveryCode)
        ];
    }
}
