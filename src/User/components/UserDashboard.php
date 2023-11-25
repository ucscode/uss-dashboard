<?php

use Ucscode\TreeNode\TreeNode;

class UserDashboard extends AbstractDashboard implements UserDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $profileBatch;

    /**
     * This is the entry method for any class that extends AbstractDashboard
     */
    public function createProject(DashboardConfig $config): void
    {
        parent::createProject($config);
        $this->profileBatch = new TreeNode('profileBatch');
        $this->inventPages();
        $this->beforeRender();
    }

    /**
     * @method inventPages
     */
    protected function inventPages(): void
    {
        $pageCollection = [...$this->inventAuthPages(), ...$this->inventMainPages()];
        foreach($pageCollection as $pageManager) {
            $this->pageRepository
                ->addPageManager($pageManager->name, $pageManager);
        };
    }

    /**
     * @method inventAuthPages
     */
    protected function inventAuthPages(): iterable
    {
        yield $this->createPage(PageManager::LOGIN, false)
            ->setForm(UserLoginForm::class)
            ->setTemplate($this->useTheme('/pages/user/security/login.html.twig'));

        yield $this->createPage(self::PAGE_REGISTER)
            ->setController(UserRegisterController::class)
            ->setForm(UserRegisterForm::class)
            ->setTemplate($this->useTheme('/pages/user/security/register.html.twig'));

        yield $this->createPage(self::PAGE_RECOVERY)
            ->setController(UserRecoveryController::class)
            ->setForm(UserRecoveryForm::class)
            ->setTemplate($this->useTheme('/pages/user/security/recovery.html.twig'));

        $logoutNavigation = [
            'label' => 'logout',
            'href' => $this->urlGenerator('/' . self::PAGE_LOGOUT),
            'icon' => 'bi bi-power',
            'order' => 1024
        ];

        yield $this->createPage(self::PAGE_LOGOUT)
            ->setTemplate(null)
            ->setController(UserLogoutController::class)
            ->setCustom('endpoint', $this->urlGenerator('/'))
            ->addMenuItem(self::PAGE_LOGOUT, $logoutNavigation, $this->userMenu);
    }

    /**
     * @method inventMainPages
     */
    protected function inventMainPages(): iterable
    {
        $dashboardNavigation = new TreeNode('dashboard', [
            'label' => 'dashboard',
            'href' => $this->urlGenerator('/'),
            'icon' => 'bi bi-speedometer',
        ]);

        yield $this->createPage(self::PAGE_INDEX)
            ->setRoute('/')
            ->setController(UserIndexController::class)
            ->setTemplate($this->useTheme('/pages/user/index.html.twig'))
            ->addMenuItem(self::PAGE_INDEX, $dashboardNavigation, $this->menu);

        yield $this->createPage(self::PAGE_NOTIFICATIONS)
            ->setController(UserNotificationController::class)
            ->setTemplate($this->useTheme('/pages/notifications.html.twig'));

        yield $this->createProfilePage();

        $passwordPillNavigation = [
            'label' => 'password',
            'href' => $this->urlGenerator('/' . self::PAGE_USER_PASSWORD),
            'icon' => 'bi bi-unlock'
        ];

        yield $this->createPage(self::PAGE_USER_PASSWORD)
            ->setController(UserPasswordController::class)
            ->setTemplate($this->useTheme('/pages/user/profile/password.html.twig'))
            ->addMenuItem('passwordPill', $passwordPillNavigation, $this->profileBatch);
    }

    /**
     * @method createProfilePage
     */
    protected function createProfilePage(): PageManager
    {
        $profileNavigation = [
            'label' => 'Profile',
            'href' => $this->urlGenerator('/' . self::PAGE_USER_PROFILE),
            'icon' => 'bi bi-person'
        ];
        
        $profilePillNavigation = [
            'label' => 'Profile',
            'href' => $this->urlGenerator('/' . self::PAGE_USER_PROFILE),
            'icon' => 'bi bi-person-circle',
        ];
        
        return $this->createPage(self::PAGE_USER_PROFILE)
            ->setController(UserProfileController::class)
            ->setTemplate($this->useTheme('/pages/user/profile/main.html.twig'))
            ->addMenuItem(self::PAGE_USER_PROFILE, $profileNavigation, $this->menu)
            ->addMenuItem('profilePill', $profilePillNavigation, $this->profileBatch);
    }

    /**
     * @method beforeRender
     */
    protected function beforeRender(): void
    {
        $pageManager = $this->pageRepository->getPageManager(self::PAGE_USER_PROFILE);

        $profileBatchRegulator = new class ($this->profileBatch, $pageManager) implements EventInterface 
        {
            public function __construct(
                protected TreeNode $profileBatch,
                protected PageManager $pageManager
            )
            {}

            public function eventAction(array|object $data): void
            {
                $this->profileBatch->sortChildren(function(TreeNode $a, TreeNode $b) {
                    return ($a->getAttr('order') ?? 0) <=> ($b->getAttr('order') ?? 0);
                });
                $this->inspectActiveItem();
            }

            public function inspectActiveItem(): void
            {
                foreach($this->profileBatch->children as $child) {
                    if($child->getAttr('active') && $this->pageManager) {
                        $this->pageManager
                            ->getMenuItem(UserDashboardInterface::PAGE_USER_PROFILE, true)
                            ?->setAttr('active', true);
                    }
                };
            }
        };

        (new Event())->addListener('dashboard:render', $profileBatchRegulator, -10);
    }
}
