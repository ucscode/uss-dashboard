<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Uss\Component\Kernel\Uss;

class OnCreateSubmit extends AbstractUserFormSubmit
{
    public function onValidate(?array &$resource, AbstractDashboardForm $form): void
    {
        $resource['usercode'] = Uss::instance()->keygen(7);
        parent::onValidate($resource, $form);
        $form->setProperty(CrudEditorForm::PERSISTENCE_ENABLED, false);
    }

    public function onPersist(mixed &$response, AbstractDashboardForm $form): void
    {
        $persisted = $form->getProperty(CrudEditorForm::PERSISTENCE_STATUS);
        if(!$persisted) {
            if($this->postContext['notify_client'] ?? false) {
                // var_dump('notify-client');
            }
        }
        parent::onPersist($response, $form);
    }
}