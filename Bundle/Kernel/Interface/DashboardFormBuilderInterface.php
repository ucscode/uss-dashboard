<?php

namespace Module\Dashboard\Bundle\Kernel\Interface;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;

interface DashboardFormBuilderInterface
{
    public function onBuild(AbstractDashboardForm $form): void;
}