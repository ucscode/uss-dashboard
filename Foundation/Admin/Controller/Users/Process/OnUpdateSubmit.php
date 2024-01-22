<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormSubmitInterface;

class OnUpdateSubmit implements DashboardFormSubmitInterface
{
    public function onFilter(array &$resource, AbstractDashboardForm $form): void
    {}

    public function onValidate(array &$resource, AbstractDashboardForm $form): void
    {
        $resource['password'] = password_hash($resource['password'], PASSWORD_DEFAULT);
    }

    public function onPersist(mixed &$response, AbstractDashboardForm $form): void
    {
        
    }
}