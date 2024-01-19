<?php

namespace Module\Dashboard;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Foundation\DatabaseGenerator;
use Module\Dashboard\Bundle\Kernel\Service\AppControl;
use Module\Dashboard\Bundle\Immutable\RoleImmutable;
use Module\Dashboard\Bundle\Kernel\Compact\DashboardEnvironment;
use Module\Dashboard\Foundation\Admin\AdminDashboard;
use Module\Dashboard\Foundation\System\Api\Notification\NotificationApi;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Block\Block;
use Uss\Component\Block\BlockManager;
use Uss\Component\Event\Event;
use Uss\Component\Kernel\Uss;

define('USS_DASHBOARD_DIR', __DIR__);

new class () {
    public function __construct()
    {
        $uss = Uss::instance();

        $this->createSystemApplication($uss);
        $this->createUserApplication();
        $this->createAdminApplication();

        (new Event())->addListener(
            'modules:loaded',
            fn () => Event::emit('dashboard:render'),
            1024
        );
    }

    protected function createSystemApplication(Uss $uss): void
    {
        new DatabaseGenerator($uss);
        new DashboardEnvironment($uss);

        $userAvatar = DashboardImmutable::GUI_DIR . '/assets/images/user.png';
        $uss->twigContext['default_user_avatar'] = $uss->pathToUrl($userAvatar);
        
        BlockManager::instance()->addBlock("dashboard_content", new Block(true));
        BlockManager::instance()->addBlock("profile_content", new Block(true));

        new NotificationApi();
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
