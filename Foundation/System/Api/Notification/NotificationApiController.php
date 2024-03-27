<?php

namespace Module\Dashboard\Foundation\System\Api\Notification;

use Module\Dashboard\Bundle\User\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Ucscode\SQuery\Condition;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;
use Uss\Component\Route\RouteInterface;

class NotificationApiController implements RouteInterface
{
    const PAYLOAD_MARK_AS_READ = 'mark-as-read';
    const PAYLOAD_MARK_AS_HIDDEN = 'mark-as-hidden';
    const PAYLOAD_GET = 'get';
    const PAYLOAD_REMOVE = 'remove';

    protected ?array $payload;
    protected Uss $uss;
    protected User $user;

    public function onload(ParameterBag $container): Response
    {
        $this->payload = json_decode(file_get_contents("php://input"), true);
        $this->uss = Uss::instance();
        $this->user = (new User())->acquireFromSession();
        return $this->verifyNonce() ?? $this->handleRequest();
    }

    protected function verifyNonce(): ?Response
    {
        $isValid = 
            $this->payload && 
            $this->uss->nonce(
                $_SESSION[UssImmutable::APP_SESSION_KEY], 
                $this->payload['nonce'] ?? ''
            );
        
        return !$isValid ? $this->createJsonResponse(false, "Cannot validate security token") : null;
    }

    protected function handleRequest(): Response
    {
        $data = $this->uss->sanitize($this->payload['data'], true);
        
        $jsonResponse = match($this->payload['request']) {

            self::PAYLOAD_MARK_AS_READ => $this->markAsRead($data),

            self::PAYLOAD_MARK_AS_HIDDEN => $this->markAsHidden($data),

            self::PAYLOAD_GET => $this->getEntity($data, $this->payload['count']),

            self::PAYLOAD_REMOVE => $this->removeEntity($data, $this->payload['count']),

            default => $this->createJsonResponse(false, "Invalid Notification Request")
        };

        return $jsonResponse;
    }

    protected function markAsRead(array $data): JsonResponse
    {
        $response = $this->markEntityAs(array('seen' => $data['read']), $data['indexes']);
        $status = $response !== null;
        $message = sprintf(
            $status ? "Notification successfully marked as %s" : "Notification could not be marked as %s",
            empty($data['read']) ? 'unread' : 'read'
        );
        return $this->createJsonResponse($status, $message, $response);
    }

    protected function markAsHidden(array $data): JsonResponse
    {
        $response = $this->markEntityAs(array('hidden' => $data['hidden']), $data['indexes']);
        $status = $response !== null;
        $message = sprintf(
            $status ? "Notification successfully marked as %s" : "Notification could not be marked as %s",
            empty($data['hidden']) ? 'not hidden' : 'hidden'
        );
        return $this->createJsonResponse($status, $message, $response);
    }

    protected function getEntity(array $data, int $count): JsonResponse
    {
        $response = $this->user->notification->{$count ? "count" : "get"}($data, 0, null);
        $status = !empty($response);
        $message = $status ? "Notification items retrieved" : "No items found";
        return $this->createJsonResponse($status, $message, $this->refactor($response));
    }

    protected function removeEntity(array $data, int $count): JsonResponse
    {
        $response = $this->user->notification->{$count ? "count" : "get"}($data, 0, null);
        $status = $this->user->notification->remove($data);
        $message = $status ? 
            "Notification items successfully removed" : 
            "Notification items removal failed";
        return $this->createJsonResponse($status, $message, $this->refactor($response));
    }

    protected function markEntityAs(array $query, array $indexes): ?array
    {
        $condition = new Condition();
        !empty($indexes) ? $condition->add("id", $indexes) : null;
        $status = $this->user->notification->update($query, $condition);
        $associates = $this->user->notification->get($condition, 0, null);
        return $status && !empty($associates) ? $this->refactor($associates) : null;
    }

    protected function refactor(int|array $entities): array
    {
        return [
            'pending' => $this->user->notification->count([
                'hidden' => 0,
                'seen' => 0,
            ]),
            'entities' => $this->mapEntity($entities),
        ];
    }

    protected function mapEntity(int|array $entity): int|array
    {
        if(is_array($entity)) {
            return array_map(function($item) {
                $item['period'] = $this->uss->relativeTime($item['period']);
                unset($item['internal_note']);
                if(empty($item['avatar_url'])) {
                    $item['avatar_url'] = $this->uss->templateContext['default_user_avatar'];
                }
                return $item;
            }, $entity);
        };
        return $entity;
    }

    protected function createJsonResponse(bool $status, string $message, array $data = []): JsonResponse
    {
        return new JsonResponse([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }
}
