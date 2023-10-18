<?php

class UserLogoutController implements RouteInterface
{
    public function onload($pageInfo)
    {
        if(isset($_SESSION[UserInterface::SESSION_KEY])) {
            unset($_SESSION[UserInterface::SESSION_KEY]);
        };

        $endpoint = $pageInfo['endpoint'] ?? null;

        if(!($endpoint instanceof UrlGenerator) && !is_string($endpoint)) {
            $endpoint = UserDashboard::instance()->urlGenerator();
        };

        header("location: " . $endpoint);
        exit;
    }

};
