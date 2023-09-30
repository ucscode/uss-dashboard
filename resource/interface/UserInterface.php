<?php

interface UserInterface
{
    public function exists(): bool;

    public function persist(): bool;

    public function delete(): ?bool;

    public function getRoles(): array;

    public function addRole(string|array $role): bool;

    public function removeRole(string|array $role): bool;

    public function hasRole(string $role): bool;

    public function getMeta(string $key, bool $epoch): mixed;

    public function setMeta(string $key, mixed $value): bool;

    public function removeMeta(string $key): ?bool;

    public function getAllMeta(string $regex): array;

    public function errors(): array;

    //public function addNotification(array $data): ?array;

    //public function removeNotification(array $data): ?array;

    //public function updateNotification(array $data): ?array;

    //public function getNotification(array $data): ?array;

}
