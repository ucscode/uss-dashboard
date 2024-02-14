<?php

namespace Module\Dashboard;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Service\AppControl;
use Module\Dashboard\Bundle\Immutable\RoleImmutable;
use Module\Dashboard\Bundle\Kernel\Compact\DashboardEnvironment;
use Module\Dashboard\Foundation\Admin\AdminDashboard;
use Module\Dashboard\Foundation\System\Api\Notification\NotificationApi;
use Module\Dashboard\Foundation\System\Compact\DatabaseGenerator;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Block\Block;
use Uss\Component\Block\BlockManager;
use Uss\Component\Event\Event;
use Uss\Component\Kernel\Uss;

new class () {
    protected Uss $uss;

    public function __construct()
    {
        $this->uss = Uss::instance();
        $this->defineAppConstants();
        $this->createSystemApplication($this->uss);
        $this->createUserApplication();
        $this->createAdminApplication();
        (new Event())->addListener('modules:loaded', fn () => Event::emit('dashboard:render'), 1024);
    }

    protected function defineAppConstants(): void
    {
        define('USS_DASHBOARD_DIR', __DIR__);
        defined('ENV_DB_PREFIX') ?: define('ENV_DB_PREFIX', $_ENV['DB_PREFIX'] ?? '');
    }

    protected function createSystemApplication(Uss $uss): void
    {
        new DatabaseGenerator($uss);
        new DashboardEnvironment($uss);
        BlockManager::instance()->addBlock("dashboard_content", new Block(true));
        $userAvatar = DashboardImmutable::GUI_DIR . '/assets/images/user.png';
        $uss->templateContext['default_user_avatar'] = $uss->pathToUrl($userAvatar);
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
            ->setThemeFolder('douglas')
            ->setPermissions([
                RoleImmutable::ROLE_SUPER_ADMIN,
                RoleImmutable::ROLE_ADMIN,
            ]);
            
        AdminDashboard::instance($appControl); // One-time Instantiation
    }
};
