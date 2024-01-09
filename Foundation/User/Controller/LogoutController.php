<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\User\User;
use Uss\Component\Manager\UrlGenerator;

class LogoutController extends AbstractDashboardController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        (new User())->acquireFromSession()->destroySession();

        $endpoint = $document->getCustom('endpoint');
        $endpoint = 
            $endpoint instanceof UrlGenerator || 
            is_string($endpoint) ? $endpoint : $dashboard->urlGenerator();
        
        header("location: " . $endpoint);
        exit;
    }
};
