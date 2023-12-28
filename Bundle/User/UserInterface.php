<?php

namespace Module\Dashboard\Bundle\User;

use Uss\Component\Database;

interface UserInterface
{
    public const USER_TABLE = Database::PREFIX . "users";
    public const META_TABLE = Database::PREFIX . "usermeta";
    public const SESSION_KEY = 'user:session';

    public function isAvailable(): bool;
    public function persist(): bool;
    public function delete(): ?bool;
    public function isValidPassword(string $password): bool;
}
