<?php

// get Uss Instance
$uss = Uss::instance();

// Create Dashboard Database
new DatabaseConfigurator();

// Initialize UserMeta
User::initialize();

// Register Email & Theme Template Directory
$uss->addTwigFilesystem(DashboardImmutable::MAIL_TEMPLATE_DIR, 'Mail');
$uss->addTwigFilesystem(DashboardImmutable::THEME_DIR, 'Theme');

// Configure User Dashboard
$userDashboardConfig = (new DashboardConfig())
    ->setBase('/dashboard')
    ->setTheme('default')
    ->addPermission(RoleImmutable::ROLE_USER);

UserDashboard::instance()->createProject($userDashboardConfig);

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

AdminDashboard::instance()->createProject($adminDashboardConfig);

/**
 * Global Modules Configuration;
 */
(new Event())->addListener('modules:loaded', function () {
    Event::emit('dashboard:render');
}, -9);
