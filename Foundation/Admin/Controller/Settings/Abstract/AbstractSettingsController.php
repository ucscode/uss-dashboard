<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Settings\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;

abstract class AbstractSettingsController extends AbstractDashboardController
{
    public function onload(array $context): void
    {
        parent::onload($context);
        $layout = '@Foundation\Admin\Template\settings\fragment\layout.html.twig';
        $this->document->setThemeBaseLayout($layout);
    }
}