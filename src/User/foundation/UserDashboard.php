<?php

use Ucscode\TreeNode\TreeNode;

class UserDashboard extends AbstractDashboard implements UserDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $profileBatch;
    
    public function createProject(DashboardConfig $config): void
    {
        parent::createProject($config);

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
