<?php

interface UserDashboardInterface 
{
    public const DIR = DashboardImmutable::SRC_DIR . '/User';
    public const ASSETS_DIR = self::DIR . '/assets';
    public const FORMS_DIR = self::DIR . '/forms';
    public const CONTROLLER_DIR = self::DIR . '/controllers';
    public const TEMPLATE_DIR = self::DIR . "/templates";

    public const PAGE_LOGIN = 'login';
    public const PAGE_LOGOUT = 'logout';
    public const PAGE_REGISTER = 'register';
    public const PAGE_RECOVERY = 'recovery';
    public const PAGE_INDEX = 'index';
    public const PAGE_NOTIFICATIONS = 'notifications';
    public const PAGE_USER_PROFILE = 'profile';
    public const PAGE_USER_PASSWORD = 'password';
}