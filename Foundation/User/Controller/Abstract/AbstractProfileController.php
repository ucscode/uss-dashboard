<?php

namespace Module\Dashboard\Foundation\User\Controller\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;

abstract class AbstractProfileController extends AbstractDashboardController
{
    public function onload(array $context): void
    {
        parent::onload($context);
        $layout = '@Foundation/User/Template/profile/layout.html.twig';
        $this->document->setThemeBaseLayout($layout);
    }
}
