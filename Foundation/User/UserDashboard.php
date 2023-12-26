<?php

namespace Module\Dashboard\Foundation\User;

use Module\Dashboard\Bundle\Kernel\AbstractDashboard;
use Module\Dashboard\Bundle\Kernel\AppControl;
use Ucscode\TreeNode\TreeNode;
use Uss\Component\Trait\SingletonTrait;

class UserDashboard extends AbstractDashboard implements UserDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $profileBatch;
    
    public function createApp(AppControl $appControl): void
    {
        parent::createApp($appControl);
        
        $this->profileBatch = new TreeNode('profileBatch');

        $factory = new UserPageFactory($this);
        
        $factory->createLoginPage();
        $factory->createRegisterPage();
        $factory->createRecoveryPage();
        $factory->createLogoutPage();
        $factory->createIndexPage();
        $factory->createNotificationPage();
        $factory->createUserProfilePage();
        $factory->createUserPasswordPage();

        (new Event())->addListener('dashboard:render', new ProfileBatchRegulator($this), -10);
    }
}
