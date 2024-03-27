<?php

namespace Module\Dashboard\Foundation\User\Controller\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractProfileController extends AbstractDashboardController
{
    public function onload(ParameterBag $container): Response
    {
        parent::onload($context);
        $layout = '@Foundation/User/Template/profile/layout.html.twig';
        $this->document->setThemeBaseLayout($layout);
    }
}
