<?php

namespace Module\Dashboard\Bundle\User\Interface;

use Uss\Component\Database;

interface UserConstInterface
{
    public const SESSION_KEY = 'user:session';
    public const TABLE_USER = Database::PREFIX . "users";
    public const TABLE_META = Database::PREFIX . "usermeta";
}