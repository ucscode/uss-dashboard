<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Module\Dashboard\Bundle\Mailer\Mailer;
use Module\Dashboard\Bundle\User\User;
use Uss\Component\Kernel\Uss;

class OnCreateSubmit extends AbstractUserFormSubmit
{
    public function onValidate(?array &$resource, AbstractDashboardForm $form): void
    {
        $resource['usercode'] = Uss::instance()->keygen(7);
        parent::onValidate($resource, $form);
    }

    public function onPersist(mixed &$response, AbstractDashboardForm|CrudEditorForm $form): void
    {
        if($form->isPersisted() && $response) {
            $this->client = new User($form->getPersistenceLastInsertId());
            if($this->postContext['notify_client'] ?? false) {
                $this->sendUserCreationEmail($form->getProperty('crud:create.email.loginUrl'));
            }
        }
        parent::onPersist($response, $form);
        $form->replaceHistoryState(false);
        
    }

    protected function sendUserCreationEmail(?string $redirectUrl): void
    {
        $template = '@Foundation/Admin/Template/users/mails/create-user.html.twig';

        $mailer = new Mailer();

        $mailer
            ->setTemplate($template, [
                'companyName' => Uss::instance()->options->get('company:name'),
                'loginEmail' => $this->client->getEmail(),
                'loginPassword' => $this->postContext['password'],
                'redirectUrl' => $redirectUrl,
            ])
            ->setSubject('New Account Created')
            ->addAddress($this->client->getEmail())
            ->useMailHogTesting($mailer->isLocalhost())
        ;

        if(!$mailer->sendMail()) {
            $this->easyToast("Notification email not sent", null, 1000);
        }
    }
}