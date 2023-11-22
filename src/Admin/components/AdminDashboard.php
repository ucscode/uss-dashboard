<?php

use Ucscode\TreeNode\TreeNode;

class AdminDashboard extends AbstractDashboard implements AdminDashboardInterface
{
    use SingletonTrait;

    public function createProject(DashboardConfig $config): void
    {
        parent::createProject($config);
        $this->registerPageManagers();
    }

    protected function registerPageManagers(): void
    {
        $pageManagers = $this->createPageManagers();
        foreach($pageManagers as $pageManager) {
            $this->pageRepository->addPageManager($pageManager->name, $pageManager);
        }
    }

    protected function createPageManagers(): iterable
    {
        $dashboard = UserDashboard::instance();

        yield (new PageManager(PageManager::LOGIN))
            ->setForm(AdminLoginForm::class)
            ->setTemplate($this->useTheme('/pages/admin/security/login.html.twig'));

        yield (new PageManager('index'))
            ->setController(AdminIndexController::class)
            ->setRoute('/')
            ->setTemplate($this->useTheme('/pages/admin/index.html.twig'))
            ->addMenuItem('index', [
                'label' => 'Dashboard',
                'icon' => 'bi bi-microsoft',
                'href' => $this->urlGenerator()
            ], $this->menu);

        yield (new PageManager('logout'))
            ->setController(UserLogoutController::class)
            ->setRoute('/logout')
            ->addMenuItem('logout', [
                'icon' => 'bi bi-power',
                'order' => 1024,
                'label' => 'logout',
                'href' => $this->urlGenerator('/logout')
            ], $this->userMenu)
            ->setCustom('endpoint', $this->urlGenerator());

        yield (new PageManager('notifications'))
            ->setRoute('/notifications')
            ->setController(UserNotificationController::class)
            ->setTemplate($this->useTheme('/pages/notifications.html.twig'));

        yield (new PageManager('users'))
            ->setRoute('/users')
            ->setController(AdminUserController::class)
            ->setTemplate($this->useTheme('/pages/admin/users.html.twig'))
            ->addMenuItem('users', $this->createUserMenuItem(), $this->menu);
        
        yield (new PageManager('settings'))
            ->setRoute('/settings')
            ->setController(AdminSettingsController::class)
            ->setTemplate($this->useTheme('/pages/admin/settings.html.twig'))
            ->addMenuItem('settings', [
                'label' => 'settings',
                'href' => $this->urlGenerator('/settings'),
                'icon' => 'bi bi-cog'
            ], $this->menu);
    }

    protected function createUserMenuItem(): TreeNode
    {
        $parentItem = new TreeNode('users', [
            'label' => 'Users',
            'icon' => 'bi bi-people-fill',
            'href' => $this->urlGenerator('/users'),
        ]);
        return $parentItem;
    }
}
