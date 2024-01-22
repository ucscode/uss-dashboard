<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormSubmitInterface;

class OnCreateSubmit implements DashboardFormSubmitInterface
{
    public function onSubmit(array &$resource, AbstractDashboardForm $form): void
    {
        
    }

    public function onValidate(array &$resource, AbstractDashboardForm $form): void
    {

    }

    public function onPersist(mixed &$response, AbstractDashboardForm $form): void
    {
        
    }
}