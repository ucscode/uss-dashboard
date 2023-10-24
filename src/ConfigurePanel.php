<?php

new DatabaseConfigurator();

/**
 * Register Email Template Directory
 */
Uss::instance()->addTwigFilesystem(DashboardImmutable::MAIL_TEMPLATE_DIR, 'mail');

/**
 * Configure User Dashboard
 */
$userDashboardConfig = (new DashboardConfig())
    ->setBase('/dashboard')
    ->setTemplateFilesystem(UserDashboard::TEMPLATE_DIR, 'Ud');

UserDashboard::instance()->configureDashboard($userDashboardConfig);

/**
 * Configure Admin Dashboard
 */
$adminDashboardConfig = (new DashboardConfig())
    ->setBase("/admin")
    ->setTemplateFilesystem(AdminDashboard::TEMPLATE_DIR, 'Ua');

AdminDashboard::instance()->configureDashboard($adminDashboardConfig);

/**
 * Global Modules Configuration;
 */
Event::instance()->addListener('modules:loaded', function () {
    Event::instance()->emit('dashboard:render');
}, -9);
