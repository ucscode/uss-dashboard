<?php

namespace Module\Dashboard\Bundle\User;

use Uss\Component\Database;

interface UserInterface
{
    public const USER_TABLE = Database::PREFIX . "users";
    public const META_TABLE = Database::PREFIX . "usermeta";
    public const NOTIFICATION_TABLE = Database::PREFIX . "notifications";
    public const SESSION_KEY = 'user:session';

    public function exists(): bool;
    public function persist(): bool;
    public function delete(): ?bool;
    public function isValidPassword(string $password): bool;

    public function getUserMeta(string $key, bool $epoch): mixed;
    public function getUserMetaByRegex(string $regex): array;
    public function setUserMeta(string $key, mixed $value): bool;
    public function removeUserMeta(string $key): ?bool;

    public function addNotification(array $data): int|bool;
    public function updateNotification(array $data, int|array $filter): bool;
    public function getNotifications(array $filter, int $start, int $limit, string $order): ?array;
    public function removeNotification(int|array $filter): bool;
    public function countNotifications(array $filter): int;

    public function getRoles(?int $index): array|string|null;
    public function hasRole(string $role): bool;
    public function addRole(string $role): bool;
    public function setRoles(array $roles): bool;
    public function removeRole(string $role): bool;
}
