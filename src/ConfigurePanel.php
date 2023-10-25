<?php

new DatabaseConfigurator();

/**
 * Register Email Template Directory
 */
$uss = Uss::instance();
$uss->addTwigFilesystem(DashboardImmutable::MAIL_TEMPLATE_DIR, 'Mail');
$uss->addTwigFilesystem(DashboardImmutable::THEME_DIR, 'Theme');

/**
 * Configure User Dashboard
 */
$userDashboardConfig = (new DashboardConfig())
    ->setBase('/dashboard')
    ->setTheme('default')
    ->addPermission(RoleImmutable::ROLE_USER);

UserDashboard::instance()->configureDashboard($userDashboardConfig);

/**
 * Configure Admin Dashboard
 */
$adminDashboardConfig = (new DashboardConfig())
    ->setBase("/admin")
    ->setTheme('default')
    ->setPermissions([
        RoleImmutable::ROLE_SUPERADMIN,
        RoleImmutable::ROLE_ADMIN
    ]);

AdminDashboard::instance()->configureDashboard($adminDashboardConfig);

/**
 * Global Modules Configuration;
 */
Event::instance()->addListener('modules:loaded', function () {
    Event::instance()->emit('dashboard:render');
}, -9);
