<?php

namespace Module\Dashboard\Foundation\User;

use Module\Dashboard\Bundle\Kernel\Service\Interface\AppControlInterface;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use Module\Dashboard\Bundle\Kernel\Compact\DashboardMenuFormation;
use Module\Dashboard\Foundation\User\Compact\AjaxDocumentFactory;
use Module\Dashboard\Foundation\User\Compact\DocumentFactory;
use Module\Dashboard\Foundation\User\Compact\Interface\UserDashboardInterface;
use Ucscode\TreeNode\TreeNode;
use Uss\Component\Block\Block;
use Uss\Component\Block\BlockManager;
use Uss\Component\Trait\SingletonTrait;
use Uss\Component\Event\Event;

class UserDashboard extends AbstractDashboard implements UserDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $profileBatch;

    public function __construct(AppControlInterface $appControl)
    {
        parent::__construct($appControl);

        $this->profileBatch = new TreeNode('profileBatch');
        
        BlockManager::instance()->addBlock("profile_content", new Block(true));

        $this->createLocalDocuments();
        $this->createAjaxDocuments();
        
        Event::instance()->addListener('modules:loaded', function() {
            new DashboardMenuFormation(
                $this->profileBatch,
                $this->getDocument('user.profile')?->getMenuItem('main:profile')
            );
        }, -10);
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
        $this->addDocument('user.profile', $factory->createUserProfileDocument());
        $this->addDocument('user.profile.password', $factory->createUserProfilePasswordDocument());
    }

    protected function createAjaxDocuments(): void
    {
       $ajaxFactory = new AjaxDocumentFactory($this);
       $this->addDocument('ajax:verify-email', $ajaxFactory->createResendRegisterEmailDocument());
    }
}
