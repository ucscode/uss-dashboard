<?php

namespace Module\Dashboard\Bundle\Kernel\Interface;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;

interface DashboardFormSubmitInterface
{
    public function onSubmit(array &$resource, AbstractDashboardForm $form): void;
    public function onValidate(array &$resource, AbstractDashboardForm $form): void;
    public function onPersist(mixed &$response, AbstractDashboardForm $form): void;
}