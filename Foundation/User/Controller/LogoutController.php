<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Manager\UrlGenerator;
use Uss\Component\Route\RouteInterface;

class LogoutController implements RouteInterface
{
    public function onload($match)
    {
        (new User())
            ->acquireFromSession()
            ->destroySession();

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
