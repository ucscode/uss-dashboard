<?php

namespace Module\Dashboard\Foundation\User;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use Module\Dashboard\Bundle\Kernel\Compact\DashboardMenuFormation;
use Module\Dashboard\Bundle\Kernel\Service\AppControl;
use Module\Dashboard\Foundation\User\Compact\AjaxDocumentFactory;
use Module\Dashboard\Foundation\User\Compact\DocumentFactory;
use Ucscode\TreeNode\TreeNode;
use Uss\Component\Trait\SingletonTrait;
use Uss\Component\Event\Event;

class UserDashboard extends AbstractDashboard implements UserDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $profileBatch;

    public function createApp(AppControl $appControl): void
    {
        $this->profileBatch = new TreeNode('profileBatch');

        parent::createApp($appControl);
        $this->createLocalDocuments();
        $this->createAjaxDocuments();

        (new Event())->addListener(
            'modules:loaded', 
            fn () => new DashboardMenuFormation($this->profileBatch), 
            -10
        );
    }

    protected function createLocalDocuments(): void
    {
        $factory = new DocumentFactory($this, '@Foundation/User/Template');

        $this->addDocument('login', $factory->createLoginDocument());
        $this->addDocument('register', $factory->createRegisterDocument());
        $this->addDocument('recovery', $factory->createPasswordResetDocument());
        $this->addDocument('logout', $factory->createLogoutDocument());
        $this->addDocument('index', $factory->createIndexDocument());
        $this->addDocument('notifications', $factory->createNotificationDocument());
        $this->addDocument('userProfile', $factory->createUserProfileDocument());
        $this->addDocument('userProfilePassword', $factory->createUserProfilePasswordDocument());
    }

    protected function createAjaxDocuments(): void
    {
       $ajaxFactory = new AjaxDocumentFactory($this);

       $this->addDocument('ajax:verify-email', $ajaxFactory->createResendRegisterEmailDocument());
    }
}
