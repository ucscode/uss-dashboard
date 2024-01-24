<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
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
        $persisted = $form->getProperty(CrudEditorForm::PERSISTENCE_STATUS);
        if($persisted) {
            if($this->postContext['notify_client'] ?? false) {
                $this->sendUserCreationEmail($this->postContext['email']);
            }
        }
        parent::onPersist($response, $form);
    }

    protected function sendUserCreationEmail(string $email): void
    {
        $template = '@Foundation/Admin/Template/users/mails/create-user.html.twig';
        $mailer = new Mailer();
        $mailer->setTemplate($template);
        $mailer->setSubject('New Account Created');
        $mailer->addAddress($email);
        echo $mailer->getRenderContent($template);
        exit;
    }
}