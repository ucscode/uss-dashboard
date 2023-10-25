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
    ->setTheme('default');

UserDashboard::instance()->configureDashboard($userDashboardConfig);

/**
 * Configure Admin Dashboard
 */
$adminDashboardConfig = (new DashboardConfig())
    ->setBase("/admin")
    ->setTheme('default');

AdminDashboard::instance()->configureDashboard($adminDashboardConfig);

/**
 * Global Modules Configuration;
 */
Event::instance()->addListener('modules:loaded', function () {
    Event::instance()->emit('dashboard:render');
}, -9);
