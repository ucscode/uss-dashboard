<?php

namespace Module\Dashboard;

use Module\Dashboard\Foundation\DatabaseGenerator;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Service\AppControl;
use Module\Dashboard\Bundle\Immutable\RoleImmutable;
use Module\Dashboard\Foundation\Admin\AdminDashboard;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Event\Event;
use Uss\Component\Kernel\Uss;

define('USS_DASHBOARD_DIR', __DIR__);

new class () {
    protected Uss $uss;

    public function __construct()
    {
        $this->uss = Uss::instance();

        new DatabaseGenerator($this->uss);

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
        $this->uss
            ->filesystemLoader
            ->addPath(DashboardImmutable::FOUNDATION_DIR, 'Foundation');

        $this->uss
            ->filesystemLoader
            ->addPath(DashboardImmutable::MAILS_DIR, 'Mail');

        $this->uss
            ->filesystemLoader
            ->addPath(DashboardImmutable::THEMES_DIR, 'Theme');
    }

    protected function createUserApplication(): void
    {
        $appControl = (new AppControl())
            ->setBase('/dashboard')
            ->setThemeFolder('classic')
            ->addPermission(RoleImmutable::ROLE_USER);

        UserDashboard::instance()->createApp($appControl);
    }

    public function createAdminApplication(): void
    {
        return;
        $appControl = (new AppControl())
            ->setBase("/admin")
            ->setThemeFolder('classic')
            ->setPermissions([
                RoleImmutable::ROLE_SUPERADMIN,
                RoleImmutable::ROLE_ADMIN,
            ]);

        AdminDashboard::instance()->createApp($appControl);
    }
};
