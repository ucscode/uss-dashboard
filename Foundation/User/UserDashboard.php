<?php

namespace Module\Dashboard\Foundation\User;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
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
    }

    protected function createLocalDocuments(): void
    {
        $factory = new DocumentFactory($this, '@Foundation/User/Template');

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

    protected function createAjaxDocuments(): void
    {
       $ajaxFactory = new AjaxDocumentFactory($this);

       $this->addDocument('ajax:verify-email', $ajaxFactory->createResendRegisterEmailDocument());
    }
}
