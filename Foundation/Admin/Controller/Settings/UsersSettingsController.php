<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Settings;

use Module\Dashboard\Bundle\Document\Document;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;

class UsersSettingsController extends AbstractDashboardController
{
    public function onload(array $context): void
    {
        parent::onload($context);
    }
}