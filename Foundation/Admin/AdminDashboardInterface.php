<?php

namespace Module\Dashboard\Foundation\Admin;

use Module\Dashboard\Bundle\Kernel\DashboardInterface;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;

interface AdminDashboardInterface extends DashboardInterface
{
    public const DIR = DashboardImmutable::FOUNDATION_DIR . '/Admin';
    public const FORM_DIR = self::DIR . '/Form';
    public const CONTROLLER_DIR = self::DIR . '/Controller';
    public const COMPACT_DIR = self::DIR . '/Compact';
    public const TEMPLATE_DIR =  self::DIR . '/Template';

    public const PAGE_LOGIN = 'login';
    public const PAGE_INDEX = 'index';
    public const PAGE_LOGOUT = 'logout';
    public const PAGE_NOTIFICATIONS = 'notifications';
    public const PAGE_USERS = 'users';
    public const PAGE_SETTINGS = 'settings';
    public const PAGE_SETTINGS_DEFAULT = 'settings/default';
    public const PAGE_SETTINGS_EMAIL = 'settings/email';
    public const PAGE_SETTINGS_USERS = 'settings/users';
    public const PAGE_INFO = 'info';
}
