<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Settings;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;

class SystemSettingsController extends AbstractDashboardController
{
    public function onload(array $context): void
    {
        parent::onload($context);
        $this->form->handleSubmission();
        $this->form->build();
    }
}