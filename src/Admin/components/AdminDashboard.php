<?php

use Ucscode\TreeNode\TreeNode;

class AdminDashboard extends AbstractDashboard implements AdminDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $settingsCatalog;

    public function createProject(DashboardConfig $config): void
    {
        parent::createProject($config);
        $this->settingsCatalog = new TreeNode('settingsNode');
        $this->inventPages();
    }

    protected function inventPages(): void
    {
        $pageCollection = [...$this->getPageCollection(), ...$this->getSettingsCollection()];
        foreach($pageCollection as $pageManager) {
            $this->pageRepository
                ->addPageManager($pageManager->name, $pageManager);
        }
    }

    protected function getPageCollection(): iterable
    {
        yield $this->createPage(PageManager::LOGIN, false)
            ->setForm(AdminLoginForm::class)
            ->setTemplate($this->useTheme('/pages/admin/security/login.html.twig'));

        $indexMenuItem = [
            'label' => 'Dashboard',
            'icon' => 'bi bi-microsoft',
            'href' => $this->urlGenerator()
        ];

        yield $this->createPage(self::PAGE_INDEX)
            ->setRoute('/')
            ->setController(AdminIndexController::class)
            ->setTemplate($this->useTheme('/pages/admin/index.html.twig'))
            ->addMenuItem(self::PAGE_INDEX, $indexMenuItem, $this->menu);

        $logoutMenuItem = [
            'icon' => 'bi bi-power',
            'order' => 1024,
            'label' => 'logout',
            'href' => $this->urlGenerator('/' . self::PAGE_LOGIN)
        ];

        yield $this->createPage(self::PAGE_LOGOUT)
            ->setController(UserLogoutController::class)
            ->addMenuItem(self::PAGE_LOGOUT, $logoutMenuItem, $this->userMenu)
            ->setCustom('endpoint', $this->urlGenerator());

        yield $this->createPage(self::PAGE_NOTIFICATIONS)
            ->setController(UserNotificationController::class)
            ->setTemplate($this->useTheme('/pages/notifications.html.twig'));
            
        $userMenuItem = [
            'label' => 'Users',
            'icon' => 'bi bi-people-fill',
            'href' => $this->urlGenerator('/' . self::PAGE_USERS),
        ];

        yield $this->createPage(self::PAGE_USERS)
            ->setController(AdminUserController::class)
            ->setTemplate($this->useTheme('/pages/admin/users.html.twig'))
            ->addMenuItem(self::PAGE_USERS, $userMenuItem, $this->menu);
        
        $settingsMenuItem = [
            'label' => 'settings',
            'href' => $this->urlGenerator('/' . self::PAGE_SETTINGS),
            'icon' => 'bi bi-wrench'
        ];

        yield $this->createPage(self::PAGE_SETTINGS)
            ->setController(AdminSettingsController::class)
            ->setTemplate($this->useTheme('/pages/admin/settings.html.twig'))
            ->addMenuItem(self::PAGE_SETTINGS, $settingsMenuItem, $this->menu);
    }
    
    /**
     * @method inventSettingsPages
     */
    protected function getSettingsCollection(): iterable
    {
        $defaultItem = [
            'label' => 'Default',
            'href' => $this->urlGenerator('/' . self::PAGE_SETTINGS_DEFAULT),
            'icon' => 'bi bi-wrench'
        ];

        yield $this->createPage(self::PAGE_SETTINGS_DEFAULT)
            ->setController(AdminSettingsDefaultController::class)
            ->setTemplate($this->useTheme('/pages/admin/settings/default.html.twig'))
            ->addMenuItem('default', $defaultItem, $this->settingsCatalog);

        $emailItem = [
            'label' => 'Email',
            'href' => $this->urlGenerator('/' . self::PAGE_SETTINGS_EMAIL),
            'icon' => 'bi bi-envelope'
        ];

        yield $this->createPage(self::PAGE_SETTINGS_EMAIL)
            ->setController(AdminSettingsDefaultController::class)
            ->setTemplate($this->useTheme('/pages/admin/settings/default.html.twig'))
            ->addMenuItem('email', $emailItem, $this->settingsCatalog);

        $userItem = [
            'label' => 'Users',
            'href' => $this->urlGenerator('/' . self::PAGE_SETTINGS_USERS),
            'icon' => 'bi bi-person'
        ];

        yield $this->createPage(self::PAGE_SETTINGS_USERS)
            ->setController(AdminSettingsDefaultController::class)
            ->setTemplate($this->useTheme('/pages/admin/settings/default.html.twig'))
            ->addMenuItem('users', $userItem, $this->settingsCatalog);
    }
}
