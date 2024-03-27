<?php

namespace Module\Dashboard;

use Module\Dashboard\Bundle\Extension\DashboardExtension;
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

    public function __construct()
    {
        $this->defineAppConstants();
        $this->createSystemApplication();
        $this->createUserApplication();
        $this->createAdminApplication();

        Event::instance()->addListener(
            'modules:loaded', 
            fn () => Event::instance()->dispatch('dashboard:render'), 
            1024
        );
    }

    protected function defineAppConstants(): void
    {
        define('USS_DASHBOARD_DIR', __DIR__);
        defined('ENV_DB_PREFIX') ?: define('ENV_DB_PREFIX', $_ENV['DB_PREFIX'] ?? '');
    }

    protected function createSystemApplication(): void
    {
        new DatabaseGenerator(Uss::instance());
        new DashboardEnvironment(Uss::instance());

        BlockManager::instance()->addBlock("dashboard_content", new Block(true));

        $userAvatar = DashboardImmutable::GUI_DIR . '/assets/images/user.png';
        Uss::instance()->templateContext['default_user_avatar'] = Uss::instance()->pathToUrl($userAvatar);

        new NotificationApi();

        Uss::instance()->twig->addExtension(new DashboardExtension());
    }

    protected function createUserApplication(): void
    {
        $appControl = (new AppControl())
            ->setUrlBasePath('/dashboard')
            ->setThemeFolder('classic')
            ->addPermission(RoleImmutable::ROLE_USER);

        UserDashboard::instance($appControl); // One-time Instantiation
    }

    public function createAdminApplication(): void
    {
        $appControl = (new AppControl())
            ->setUrlBasePath("/admin")
            ->setThemeFolder('spectrum')
            ->setPermissions([
                RoleImmutable::ROLE_SUPER_ADMIN,
                RoleImmutable::ROLE_ADMIN,
            ]);
            
        AdminDashboard::instance($appControl); // One-time Instantiation
    }
};
