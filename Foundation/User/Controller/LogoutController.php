<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\User\User;
use Uss\Component\Common\UrlGenerator;

class LogoutController extends AbstractDashboardController
{
    public function onload(array $context): void
    {
        parent::onload($context);
        
        (new User())->acquireFromSession()->destroySession();

        $endpoint = $this->document->getCustom('endpoint');
        $endpoint = 
            $endpoint instanceof UrlGenerator || 
            is_string($endpoint) ? $endpoint : $this->dashboard->urlGenerator();
        
        header("location: " . $endpoint);
        exit;
    }
};
