<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Module\Dashboard\Bundle\Mailer\Mailer;
use Uss\Component\Kernel\Uss;

class OnCreateSubmit extends AbstractUserFormSubmit
{
    public function onValidate(?array &$resource, AbstractDashboardForm $form): void
    {
        $resource['usercode'] = Uss::instance()->keygen(7);
        parent::onValidate($resource, $form);
    }

    public function onPersist(mixed &$response, AbstractDashboardForm $form): void
    {
        $persisted = $form->getPersistenceStatus();
        if($persisted && $response) {
            if($this->postContext['notify_client'] ?? false) {
                $this->sendUserCreationEmail($response['email']);
            }
        }
        parent::onPersist($response, $form);
    }

    protected function sendUserCreationEmail(string $email): void
    {
        $template = '@Foundation/Admin/Template/users/mails/create-user.html.twig';
        $mailer = new Mailer();
        $mailer->setTemplate($template, [
            'companyName' => Uss::instance()->options->get('company:name'),
            'loginEmail' => $email,
            'loginPassword' => $this->postContext['password'],
        ]);
        $mailer->setSubject('New Account Created');
        $mailer->addAddress($email);
        if(!$mailer->sendMail()) {
            $this->easyToast("Notification email not sent", null, 1000);
        }
    }
}