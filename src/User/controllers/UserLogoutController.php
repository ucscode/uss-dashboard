<?php

class UserLogoutController implements RouteInterface
{
    public function onload($pageInfo)
    {

        if(isset($_SESSION['UssUser'])) {
            unset($_SESSION['UssUser']);
        };

        $endpoint = $pageInfo['endpoint'] ?? null;

        if(!($endpoint instanceof UrlGenerator) && !is_string($endpoint)) {
            $endpoint = UserDashboard::instance()->urlGenerator();
        };

        header("location: " . $endpoint);
        exit;

    }

};
