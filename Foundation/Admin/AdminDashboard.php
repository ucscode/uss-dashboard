<?php

namespace Module\Dashboard\Foundation\Admin;

use Module\Dashboard\Bundle\Kernel\AppControl;
use Module\Dashboard\Bundle\Kernel\AbstractDashboard;
use Uss\Component\Trait\SingletonTrait;
use Ucscode\TreeNode\TreeNode;

class AdminDashboard extends AbstractDashboard implements AdminDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $settingsBatch;

    public function createApp(AppControl $appControl): void
    {
        parent::createApp($appControl);

        $this->settingsBatch = new TreeNode('settingsNode');

        $factory = new AdminPageFactory($this);

        $factory->createLoginPage();
        $factory->createLogoutPage();
        $factory->createIndexPage();
        $factory->createNotificationPage();
        $factory->createUsersPage();
        $factory->createSettingsPage();
        $factory->createSettingsDefaultPage();
        $factory->createSettingsEmailPage();
        $factory->createSettingsUserPage();
        $factory->createInfoPage();

        (new Event())->addListener('dashboard:render', new SettingsBatchRegulator($this), -10);
    }
}
