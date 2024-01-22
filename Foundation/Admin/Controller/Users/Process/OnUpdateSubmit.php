<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;

class OnUpdateSubmit extends OnUserFormSubmit
{
    public function onValidate(array &$resource, AbstractDashboardForm $form): void
    {
        if(empty($resource['password'])) {
            unset($resource['password']);
            return;
        }
        parent::onValidate($resource, $form);
    }
}