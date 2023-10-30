<?php

interface UserInterface
{
    public const USER_TABLE = DB_PREFIX . "users";
    public const META_TABLE = DB_PREFIX . "usermeta";
    public const NOTIFICATION_TABLE = DB_PREFIX . "notifications";
    public const SESSION_KEY = 'user:session';

    public function exists(): bool;

    public function persist(): bool;

    public function delete(): ?bool;

    public function getUserMeta(string $key, bool $epoch): mixed;

    public function getUserMetaByRegex(string $regex): array;

    public function setUserMeta(string $key, mixed $value): bool;

    public function removeUserMeta(string $key): ?bool;

    public function addNotification(array $data): int|bool;

    public function updateNotification(array $data, int|array $filter): bool;

    public function getNotifications(array $filter, int $start, int $limit, string $order): ?array;

    public function removeNotification(int|array $filter): bool;

    public function countNotifications(array $filter): int;

    public function isValidPassword(string $password): bool;

    public function getRoles(?int $index): array|string|null;

    public function hasRole(string $role): bool;

    public function setRole(string $role): bool;

    public function removeRole(string $role): bool;
}
