<?php

namespace Module\Dashboard;

use Module\Dashboard\Foundation\DatabaseGenerator;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\AppControl;
use Module\Dashboard\Bundle\Immutable\RoleImmutable;
use Module\Dashboard\Foundation\Admin\AdminDashboard;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Event\Event;
use Uss\Component\Kernel\Uss;

define('USS_DASHBOARD_DIR', __DIR__);

new class () {
    public function __construct()
    {
        new DatabaseGenerator();

        $this->configureFilesystem();
        $this->createUserApplication();
        $this->createAdminApplication();

        (new Event())->addListener(
            'modules:loaded',
            fn () => Event::emit('dashboard:render'),
            -9
        );
    }

    protected function configureFilesystem(): void
    {
        $uss = Uss::instance();
        $uss->filesystemLoader->addPath(DashboardImmutable::MAILS_DIR, 'Mail');
        $uss->filesystemLoader->addPath(DashboardImmutable::THEMES_DIR, 'Theme');
    }

    protected function createUserApplication(): void
    {
        $appControl = (new AppControl())
            ->setBase('/dashboard')
            ->setTheme('classic')
            ->addPermission(RoleImmutable::ROLE_USER);

        UserDashboard::instance()->createApp($appControl);
    }

    public function createAdminApplication(): void
    {
        return;
        $appControl = (new AppControl())
            ->setBase("/admin")
            ->setTheme('classic')
            ->setPermissions([
                RoleImmutable::ROLE_SUPERADMIN,
                RoleImmutable::ROLE_ADMIN,
            ]);

        AdminDashboard::instance()->createApp($appControl);
    }
};
