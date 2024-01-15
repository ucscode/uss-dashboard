<?php

namespace Module\Dashboard\Foundation\System\Notification;

use Uss\Component\Route\RouteInterface;

class NotificationApiController implements RouteInterface
{
    protected ?array $payload;

    public function onload(array $routeContext): void
    {
        $this->payload = json_decode(file_get_contents("php://input"), true);
        $this->verifyNonce();
        $this->handleRequest();
    }

    protected function verifyNonce(): void
    {

    }

    protected function handleRequest(): void
    {

    }
}
