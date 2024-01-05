<?php

namespace Module\Dashboard;

use Module\Dashboard\Foundation\DatabaseGenerator;
use Module\Dashboard\Bundle\Kernel\Service\AppControl;
use Module\Dashboard\Bundle\Immutable\RoleImmutable;
use Module\Dashboard\Bundle\Kernel\DashboardEnvironment;
use Module\Dashboard\Foundation\Admin\AdminDashboard;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Event\Event;
use Uss\Component\Kernel\Uss;

define('USS_DASHBOARD_DIR', __DIR__);

new class () {
    public function __construct()
    {
        $uss = Uss::instance();

        new DatabaseGenerator($uss);
        new DashboardEnvironment($uss);

        $this->createUserApplication();
        $this->createAdminApplication();

        (new Event())->addListener(
            'modules:loaded',
            fn () => Event::emit('dashboard:render'),
            -9
        );
    }

    protected function createUserApplication(): void
    {
        $appControl = (new AppControl())
            ->setBase('/dashboard')
            ->setThemeFolder('classic')
            ->addPermission(RoleImmutable::ROLE_USER);
        UserDashboard::instance($appControl); // One-time Instantiation
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
        AdminDashboard::instance($appControl); // One-time Instantiation
    }
};
