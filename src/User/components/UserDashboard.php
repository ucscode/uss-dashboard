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

        $logoutMenuItem = [
            'label' => 'logout',
            'href' => $this->urlGenerator('/' . self::PAGE_LOGOUT),
            'icon' => 'bi bi-power',
            'order' => 1024
        ];

        yield $this->createPage(self::PAGE_LOGOUT)
            ->setTemplate(null)
            ->setController(UserLogoutController::class)
            ->setCustom('endpoint', $this->urlGenerator('/'))
            ->addMenuItem(self::PAGE_LOGOUT, $logoutMenuItem, $this->userMenu);
    }

    /**
     * @method inventMainPages
     */
    protected function inventMainPages(): iterable
    {
        $dashboardMenuItem = new TreeNode('dashboard', [
            'label' => 'dashboard',
            'href' => $this->urlGenerator('/'),
            'icon' => 'bi bi-speedometer',
        ]);

        yield $this->createPage(self::PAGE_INDEX)
            ->setRoute('/')
            ->setController(UserIndexController::class)
            ->setTemplate($this->useTheme('/pages/user/index.html.twig'))
            ->addMenuItem(self::PAGE_INDEX, $dashboardMenuItem, $this->menu);

        yield $this->createPage(self::PAGE_NOTIFICATIONS)
            ->setController(UserNotificationController::class)
            ->setTemplate($this->useTheme('/pages/notifications.html.twig'));

        yield $this->createProfilePage();

        $passwordPillItem = [
            'label' => 'password',
            'href' => $this->urlGenerator('/' . self::PAGE_USER_PASSWORD),
            'icon' => 'bi bi-unlock'
        ];

        yield $this->createPage(self::PAGE_USER_PASSWORD)
            ->setController(UserPasswordController::class)
            ->setTemplate($this->useTheme('/pages/user/profile/password.html.twig'))
            ->addMenuItem('passwordPill', $passwordPillItem, $this->profileMenu);
    }

    /**
     * @method createProfilePage
     */
    protected function createProfilePage(): PageManager
    {
        $profileMenuItem = [
            'label' => 'Profile',
            'href' => $this->urlGenerator('/' . self::PAGE_USER_PROFILE),
            'icon' => 'bi bi-person'
        ];
        
        $profilePillItem = [
            'label' => 'Profile',
            'href' => $this->urlGenerator('/' . self::PAGE_USER_PROFILE),
            'icon' => 'bi bi-person-circle',
        ];
        
        return $this->createPage(self::PAGE_USER_PROFILE)
            ->setController(UserProfileController::class)
            ->setTemplate($this->useTheme('/pages/user/profile/main.html.twig'))
            ->addMenuItem(self::PAGE_USER_PROFILE, $profileMenuItem, $this->menu)
            ->addMenuItem('profilePill', $profilePillItem, $this->profileMenu);
    }

    /**
     * @method beforeRender
     */
    protected function beforeRender(): void
    {
        (new Event())->addListener('dashboard:render', function () {
            $pageManager = $this->pageRepository->getPageManager(self::PAGE_USER_PROFILE);
            foreach($this->profileMenu->children as $child) {
                // If any child in the profile submenu is active
                if($child->getAttr('active') && $pageManager) {
                    $profileMenu = $pageManager->getMenuItem(self::PAGE_USER_PROFILE, true);
                    // Also activate the profile menu at the sidebar
                    $profileMenu?->setAttr('active', true);
                }
            };
        }, -10);
    }
}
