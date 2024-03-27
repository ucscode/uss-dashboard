<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Settings\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractSettingsController extends AbstractDashboardController
{
    public function onload(ParameterBag $container): Response
    {
        parent::onload($context);
        $layout = '@Foundation\Admin\Template\settings\layout.html.twig';
        $this->document->setThemeBaseLayout($layout);
    }
}