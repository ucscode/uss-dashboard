<?php

namespace Module\Dashboard\Foundation\Admin;

use Module\Dashboard\Bundle\Kernel\Service\Interface\AppControlInterface;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use Module\Dashboard\Bundle\Kernel\Compact\DashboardMenuFormation;
use Module\Dashboard\Foundation\Admin\Compact\DocumentFactory;
use Uss\Component\Trait\SingletonTrait;
use Ucscode\TreeNode\TreeNode;
use Uss\Component\Event\Event;

class AdminDashboard extends AbstractDashboard implements AdminDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $settingsBatch;

    public function __construct(AppControlInterface $appControl)
    {
        parent::__construct($appControl);
        $this->settingsBatch = new TreeNode('settingsNode');
        $this->createAdminDocuments();
        (new Event())->addListener('modules:loaded', fn () => new DashboardMenuFormation($this->settingsBatch), -10);
    }

    protected function createAdminDocuments(): void
    {
        $factory = new DocumentFactory($this, '@Foundation/Admin/Template');

        $this->addDocument('login', $factory->createLoginDocument());
        $this->addDocument('logout', $factory->createLogoutDocument());
        $this->addDocument('index', $factory->createIndexDocument());
        $this->addDocument('notifications', $factory->createNotificationDocument());
        $this->addDocument('recovery', $factory->createPasswordResetDocument());
        $this->addDocument('users', $factory->createUsersDocument());
        $this->addDocument('settings', $factory->createSettingsDocument());
        $this->addDocument('settings.system', $factory->createSystemSettingsDocument());
        $this->addDocument('settings.email', $factory->createEmailSettingsDocument());
        $this->addDocument('settings.users', $factory->createUsersSettingsDocument());
        $this->addDocument('systemInfo', $factory->createSystemInfoDocument());
    }
}
