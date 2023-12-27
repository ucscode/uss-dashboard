<?php

namespace Module\Dashboard\Foundation\User;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;

interface UserDashboardInterface
{
    public const DIR = DashboardImmutable::FOUNDATION_DIR . '/User';
    public const FORM_DIR = self::DIR . '/Form';
    public const CONTROLLER_DIR = self::DIR . '/Controller';
    public const COMPACT_DIR = self::DIR . '/Compact';
    public const TEMPLATE_DIR = self::DIR . '/Template';

    public const PAGE_LOGIN = 'login';
    public const PAGE_LOGOUT = 'logout';
    public const PAGE_REGISTER = 'register';
    public const PAGE_RECOVERY = 'recovery';
    public const PAGE_INDEX = 'index';
    public const PAGE_NOTIFICATIONS = 'notifications';
    public const PAGE_USER_PROFILE = 'profile';
    public const PAGE_USER_PASSWORD = 'password';
}
