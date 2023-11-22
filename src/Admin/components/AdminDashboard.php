<?php

use Ucscode\TreeNode\TreeNode;

class AdminDashboard extends AbstractDashboard implements AdminDashboardInterface
{
    use SingletonTrait;

    public function createProject(DashboardConfig $config): void
    {
        parent::createProject($config);
        $this->inventPages();
    }

    protected function inventPages(): void
    {
        $pageCollection = $this->getPageCollection();
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
}
