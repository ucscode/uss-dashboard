<?php

namespace Module\Dashboard\Foundation\Admin\Controller;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Foundation\Admin\Controller\Settings\Abstract\AbstractSettingsController;

class SettingsController extends AbstractSettingsController
{
    public function onload(array $context): void
    {
        parent::onload($context);
    }
}