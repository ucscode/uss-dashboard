<?php

use Ucscode\TreeNode\TreeNode;

class UserDashboard extends AbstractDashboard implements UserDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $profileMenu;

    /**
     * This is the entry method for any class that extends AbstractDashboard
     */
    public function createProject(DashboardConfig $config): void
    {
        parent::createProject($config);
        $this->profileMenu = new TreeNode('profileMenu');
        $this->registerArchives();
        $this->beforeRender();
    }

    /**
     * @method registerArchives
     */
    protected function registerArchives(): void
    {
        $pageManagerCollection = [
            $this->getAuthArchives(),
            $this->getPageArchives()
        ];

        foreach($pageManagerCollection as $pageManagers) {
            foreach($pageManagers as $pageManager) {
                $this->pageRepository->addPageManager($pageManager->name, $pageManager);
            }
        };

    }

    /**
     * @method beforeRender
     */
    protected function beforeRender(): void
    {
        (new Event())->addListener('dashboard:render', function () {
            foreach($this->profileMenu->children as $child) {
                if($child->getAttr('active')) {
                    $profileMenu = $this->pageRepository->getPageManager('profile')?->getMenuItem('profile', true);
                    if($profileMenu) {
                        $profileMenu->setAttr('active', true);
                    }
                }
            };
        }, -10);
    }

    /**
     * @method getAuthArchives
     */
    private function getAuthArchives(): iterable
    {
        yield (new PageManager(PageManager::LOGIN))
            ->setForm(UserLoginForm::class)
            ->setTemplate($this->useTheme('/pages/user/security/login.html.twig'));

        yield (new PageManager('register'))
            ->setRoute('/register')
            ->setController(UserRegisterController::class)
            ->setForm(UserRegisterForm::class)
            ->setTemplate($this->useTheme('/pages/user/security/register.html.twig'));

        yield (new PageManager('recovery'))
            ->setRoute('/recovery')
            ->setController(UserRecoveryController::class)
            ->setForm(UserRecoveryForm::class)
            ->setTemplate($this->useTheme('/pages/user/security/recovery.html.twig'));

        yield (new PageManager('logout'))
            ->setRoute('/logout')
            ->setTemplate(null)
            ->setController(UserLogoutController::class)
            ->setCustom('endpoint', $this->urlGenerator('/'))
            ->addMenuItem('logout', new TreeNode('logout', [
                'label' => 'logout',
                'href' => $this->urlGenerator('/logout'),
                'icon' => 'bi bi-power',
                'order' => 1024
            ]), $this->userMenu);
    }

    /**
     * @method getPageArchives
     */
    private function getPageArchives(): iterable
    {
        yield (new PageManager('index'))
            ->setRoute('/')
            ->setController(UserIndexController::class)
            ->setTemplate($this->useTheme('/pages/user/index.html.twig'))
            ->addMenuItem('index', new TreeNode('dashboard', [
                'label' => 'dashboard',
                'href' => $this->urlGenerator('/'),
                'icon' => 'bi bi-speedometer',
            ]), $this->menu);

        yield (new PageManager('notifications'))
            ->setRoute('/notifications')
            ->setController(UserNotificationController::class)
            ->setTemplate($this->useTheme('/pages/user/notifications.html.twig'));

        yield (new PageManager('profile'))
            ->setRoute('/profile')
            ->setController(UserProfileController::class)
            ->setTemplate($this->useTheme('/pages/user/profile/main.html.twig'))
            ->addMenuItem('profile', [
                'label' => 'Profile',
                'href' => $this->urlGenerator('/profile'),
                'icon' => 'bi bi-person'
            ], $this->menu)
            ->addMenuItem('profilePill', [
                'label' => 'Profile',
                'href' => $this->urlGenerator('/profile'),
                'icon' => 'bi bi-person-circle',
            ], $this->profileMenu);

        yield (new PageManager('password'))
            ->setRoute('/password')
            ->setController(UserPasswordController::class)
            ->setTemplate($this->useTheme('/pages/user/profile/password.html.twig'))
            ->addMenuItem('passwordPill', [
                'label' => 'password',
                'href' => $this->urlGenerator('/password'),
                'icon' => 'bi bi-unlock'
            ], $this->profileMenu);
    }
}
