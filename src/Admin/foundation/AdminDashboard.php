<?php

use Ucscode\TreeNode\TreeNode;

class AdminDashboard extends AbstractDashboard implements AdminDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $settingsBatch;

    public function createProject(DashboardConfig $config): void
    {
        parent::createProject($config);

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
