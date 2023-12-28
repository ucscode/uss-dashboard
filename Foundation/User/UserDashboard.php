<?php

namespace Module\Dashboard\Foundation\User;

use Module\Dashboard\Bundle\Kernel\AbstractDashboard;
use Module\Dashboard\Bundle\Kernel\AppControl;
use Module\Dashboard\Foundation\User\Compact\DocumentFactory;
use Ucscode\TreeNode\TreeNode;
use Uss\Component\Kernel\Uss;
use Uss\Component\Trait\SingletonTrait;
use Uss\Component\Event\Event;

class UserDashboard extends AbstractDashboard implements UserDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $profileBatch;

    public function createApp(AppControl $appControl): void
    {
        parent::createApp($appControl);
        Uss::instance()
            ->filesystemLoader
            ->addPath(UserDashboardInterface::TEMPLATE_DIR, 'User');
        $this->profileBatch = new TreeNode('profileBatch');
        $this->createFacade();
    }

    protected function createFacade(): void
    {
        $factory = new DocumentFactory($this, '@User');

        $this->addDocument('login', $factory->createLoginDocument());
        $this->addDocument('register', $factory->createRegisterDocument());
        $this->addDocument('recovery', $factory->createPasswordResetDocument());
        $this->addDocument('logout', $factory->createLogoutDocument());
        $this->addDocument('index', $factory->createIndexDocument());
        $this->addDocument('notification', $factory->createNotificationDocument());

        //$this->addDocument('settings', new Document());
        //$this->addDocument('profile', new Document());

        // $factory->createUserProfilePage();
        // $factory->createUserPasswordPage();

        # (new Event())->addListener('dashboard:render', new ProfileBatchRegulator($this), -10);
    }
}
