<?php

namespace Module\Dashboard;

use Module\Dashboard\Foundation\DatabaseGenerator;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\AppControl;
use Module\Dashboard\Foundation\User\UserDashboard;
use Module\Dashboard\Bundle\Immutable\RoleImmutable;
use Uss\Component\Kernel\Uss;

define('USS_DASHBOARD_DIR', __DIR__);

new class () {
    public function __construct()
    {
        new DatabaseGenerator();
        $this->configureFilesystem();
        $this->configureUserDashboard();
        $this->configureAdminDashboard();
        $this->configureDashboardOutput();
    }

    protected function configureFilesystem(): void
    {
        $uss = Uss::instance();
        $uss->filesystemLoader->addPath(DashboardImmutable::MAILS_DIR, 'Mail');
        $uss->filesystemLoader->addPath(DashboardImmutable::THEMES_DIR, 'Theme');
    }

    protected function configureUserDashboard(): void
    {
        $appControl = (new AppControl())
            ->setBase('/dashboard')
            ->setTheme('default')
            ->addPermission(RoleImmutable::ROLE_USER)
            ->setPermissionDeniedTemplate('/pages/403.html.twig');

        UserDashboard::instance()->createApp($appControl);
    }

    public function configureAdminDashboard(): void
    {
        $appControl = (new AppControl())
            ->setBase("/admin")
            ->setTheme('default')
            ->setPermissions([
                RoleImmutable::ROLE_SUPERADMIN,
                RoleImmutable::ROLE_ADMIN,
            ])
            ->setPermissionDeniedTemplate('/pages/403.html.twig');

        AdminDashboard::instance()->createApp($appControl);
    }

    protected function configureDashboardOutput(): void
    {
        (new Event())->addListener('modules:loaded', function () {
            Event::emit('dashboard:render');
        }, -9);
    }
};
