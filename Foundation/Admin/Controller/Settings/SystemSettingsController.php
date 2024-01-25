<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Settings;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;

class SystemSettingsController extends AbstractDashboardController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        
    }
}