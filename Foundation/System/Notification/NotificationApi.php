<?php

namespace Module\Dashboard\Foundation\System\Notification;

use Uss\Component\Route\Route;

class NotificationApi
{
    public function __construct()
    {
        new Route(
            "/api/notification", 
            new NotificationApiController(),
            ['POST']
        );
    }
}