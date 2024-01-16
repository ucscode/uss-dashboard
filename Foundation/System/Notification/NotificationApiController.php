<?php

namespace Module\Dashboard\Foundation\System\Notification;

use Module\Dashboard\Bundle\User\User;
use Ucscode\SQuery\Condition;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;
use Uss\Component\Route\RouteInterface;

class NotificationApiController implements RouteInterface
{
    protected ?array $payload;
    protected Uss $uss;
    protected User $user;

    public function onload(array $routeContext): void
    {
        $this->payload = json_decode(file_get_contents("php://input"), true);
        $this->uss = Uss::instance();
        $this->user = (new User())->acquireFromSession();
        $this->verifyNonce();
        $this->handleRequest();
    }

    protected function verifyNonce(): void
    {
        $isValid = 
            $this->payload && 
            $this->uss->nonce(
                $_SESSION[UssImmutable::SESSION_KEY], 
                $this->payload['nonce'] ?? ''
            );
        
        if(!$isValid) {
            $this->uss->terminate(false, "Cannot validate security token");
        }
    }

    protected function handleRequest(): void
    {
        $data = $this->uss->sanitize($this->payload['data'], true);
        
        switch($this->payload['request']) 
        {
            case 'mark-as-read':
                $response = $this->markAsValue([
                    'seen' => $data['read']
                ], $data['indexes']);
                $status = $response !== null;
                $message = $status ? 
                    "Notification successfully marked as %s" : 
                    "Notification could not be marked as %s";
                $message = sprintf($message, empty($data['read']) ? 'unread' : 'read');
                break;

            case 'mark-as-hidden':
                $response = $this->markAsValue([
                    'hidden' => $data['hidden']
                ], $data['indexes']);
                $status = $response !== null;
                $message = $status ?
                    "Notification successfully marked as %s" :
                    "Notification could not be marked as %s";
                $message = sprintf($message, empty($data['hidden']) ? 'not hidden' : 'hidden');
                break;

            case 'get':
                $response = $this->user->notification->get($data, 0, null);
                $status = !empty($response);
                $message = $status ? "Notification items retrieved" : "No items found";
                break;

            case 'remove':
                $response = $this->user->notification->get($data, 0, null);
                $status = $this->user->notification->remove($data);
                $message = $status ? 
                    "Notification items successfully removed" : 
                    "Notification items removal failed";
                break;
            
            default:
                $response = null;
                $message = "Invalid Notification Request";
        }

        $this->uss->terminate(!!$response, $message, $response);
    }

    protected function markAsValue(array $query, array $indexes): ?array
    {
        $condition = new Condition();
        !empty($indexes) ? $condition->add("id", $indexes) : null;
        $status = $this->user->notification->update($query, $condition);
        $associates = $this->user->notification->get($condition, 0, null);
        return $status && !empty($associates) ? $associates : null;
    }
}
