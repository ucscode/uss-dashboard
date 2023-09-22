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

    public function get(string $column);

    public function set(string $column, mixed $_value): void;

    public function errors(): array;

}
