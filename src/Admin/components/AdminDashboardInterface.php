<?php

interface AdminDashboardInterface
{
    public const DIR = DashboardImmutable::SRC_DIR . '/Admin';
    public const ASSETS_DIR = self::DIR . '/assets';
    public const FORM_DIR = self::DIR . '/forms';
    public const TEMPLATE_DIR = self::DIR . '/templates';
    public const CONTROLLER_DIR = self::DIR . '/controllers';

    public const PAGE_LOGIN = 'login';
    public const PAGE_INDEX = 'index';
    public const PAGE_LOGOUT = 'logout';
    public const PAGE_NOTIFICATIONS = 'notifications';
    public const PAGE_USERS = 'users';
    public const PAGE_SETTINGS = 'settings';
    public const PAGE_SETTINGS_DEFAULT = 'settings/default';
    public const PAGE_SETTINGS_EMAIL = 'settings/email';
    public const PAGE_SETTINGS_USERS = 'settings/users';
}