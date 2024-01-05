<?php

namespace Module\Dashboard\Bundle\Kernel\Interface;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;

interface DashboardFormSubmitInterface
{
    public function onSubmit(array $filteredResource, AbstractDashboardForm $form): void;
    public function onValidateResource(array $validatedResource, AbstractDashboardForm $form): void;
    public function onPersistResource(mixed $response, AbstractDashboardForm $form): void;
}