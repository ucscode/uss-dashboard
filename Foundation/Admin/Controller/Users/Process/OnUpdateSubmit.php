<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;

class OnUpdateSubmit extends AbstractUserFormSubmit
{
    public function onValidate(?array &$resource, AbstractDashboardForm $form): void
    {
        if($resource) {
            if(empty($resource['password'])) {
                unset($resource['password']);
            }
            parent::onValidate($resource, $form);
        }
    }
}