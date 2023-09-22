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

    public function get(string $key, bool $epoch): mixed;

    public function set(string $key, mixed $value): bool;

    public function remove(string $key): ?bool;

    public function getAll(string $regex): array;

    public function errors(): array;

}
