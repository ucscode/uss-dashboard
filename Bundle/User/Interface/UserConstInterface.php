<?php

namespace Module\Dashboard\Bundle\User\Interface;

use Uss\Component\Database;

interface UserConstInterface
{
    public const USER_TABLE = Database::PREFIX . "users";
    public const META_TABLE = Database::PREFIX . "usermeta";
    public const SESSION_KEY = 'user:session';
}