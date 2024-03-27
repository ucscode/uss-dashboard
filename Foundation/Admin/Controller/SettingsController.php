<?php

namespace Module\Dashboard\Foundation\Admin\Controller;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Foundation\Admin\Controller\Settings\Abstract\AbstractSettingsController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends AbstractSettingsController
{
    public function onload(ParameterBag $container): Response
    {
        parent::onload($context);
    }
}