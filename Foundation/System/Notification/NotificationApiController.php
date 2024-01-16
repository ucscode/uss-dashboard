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
                [$status, $message, $response] = $this->markAsRead($data);
                break;

            case 'mark-as-hidden': 
                [$status, $message, $response] = $this->markAsHidden($data);
                break;

            case 'get':
                [$status, $message, $response] = $this->getEntity($data, $this->payload['count']);
                break;

            case 'remove':
                [$status, $message, $response] = $this->removeEntity($data, $this->payload['count']);
                break;
            
            default:
                $status = !!($response = null);
                $message = "Invalid Notification Request";
        }

        $this->uss->terminate($status, $message, $response);
    }

    protected function markAsRead(array $data): array
    {
        $response = $this->markEntityAs([
            'seen' => $data['read']
        ], $data['indexes']);
        $status = $response !== null;
        $message = $status ? 
            "Notification successfully marked as %s" : 
            "Notification could not be marked as %s";
        $message = sprintf($message, empty($data['read']) ? 'unread' : 'read');
        return [$status, $message, $response];
    }

    protected function markAsHidden(array $data): array
    {
        $response = $this->markEntityAs([
            'hidden' => $data['hidden']
        ], $data['indexes']);
        $status = $response !== null;
        $message = $status ?
            "Notification successfully marked as %s" :
            "Notification could not be marked as %s";
        $message = sprintf($message, empty($data['hidden']) ? 'not hidden' : 'hidden');
        return [$status, $message, $response];
    }

    protected function getEntity(array $data, int $count): array
    {
        $response = $this->user->notification->{$count ? "count" : "get"}($data, 0, null);
        $status = !empty($response);
        $message = $status ? "Notification items retrieved" : "No items found";
        return [$status, $message, $this->mapEntity($response)];
    }

    protected function removeEntity(array $data, int $count): array
    {
        $response = $this->user->notification->{$count ? "count" : "get"}($data, 0, null);
        $status = $this->user->notification->remove($data);
        $message = $status ? 
            "Notification items successfully removed" : 
            "Notification items removal failed";
        return [$status, $message, $this->mapEntity($response)];
    }

    protected function markEntityAs(array $query, array $indexes): ?array
    {
        $condition = new Condition();
        !empty($indexes) ? $condition->add("id", $indexes) : null;
        $status = $this->user->notification->update($query, $condition);
        $associates = $this->user->notification->get($condition, 0, null);
        return $status && !empty($associates) ? $associates : null;
    }

    protected function mapEntity(int|array $entity): int|array
    {
        if(is_array($entity)) {
            return array_map(function($item) {
                $item['period'] = $this->uss->relativeTime($item['period']);
                unset($item['internal_note']);
                if(empty($item['avatar_url'])) {
                    $item['avatar_url'] = $this->uss->twigContext['default_user_avatar'];
                }
                return $item;
            }, $entity);
        };
        return $entity;
    }
}
