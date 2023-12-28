<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\User\UserInterface;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Manager\UrlGenerator;
use Uss\Component\Route\RouteInterface;

class LogoutController implements RouteInterface
{
    public function onload($match)
    {
        if(isset($_SESSION[UserInterface::SESSION_KEY])) {
            unset($_SESSION[UserInterface::SESSION_KEY]);
        };
        
        $dashboard = UserDashboard::instance();
        $document = $dashboard->getDocument('logout');
        $endpoint = $document->getCustom('endpoint') ?? null;

        if(!($endpoint instanceof UrlGenerator) && !is_string($endpoint)) {
            $endpoint = $dashboard->urlGenerator();
        };

        header("location: " . $endpoint);
        exit;
    }
};
