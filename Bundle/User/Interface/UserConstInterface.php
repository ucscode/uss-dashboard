<?php

namespace Module\Dashboard\Bundle\User\Interface;


interface UserConstInterface
{
    public const SESSION_KEY = 'user:session';
    public const TABLE_USER = ENV_DB_PREFIX . "users";
    public const TABLE_META = ENV_DB_PREFIX . "usermeta";
}