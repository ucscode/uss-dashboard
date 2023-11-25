<?php

use Ucscode\TreeNode\TreeNode;

class AdminDashboard extends AbstractDashboard implements AdminDashboardInterface
{
    use SingletonTrait;

    public readonly TreeNode $settingsBatch;

    public function createProject(DashboardConfig $config): void
    {
        parent::createProject($config);
        $this->settingsBatch = new TreeNode('settingsNode');
        $this->inventPages();
        $this->beforeRender();
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
            ->setTemplate($this->useTheme('/pages/admin/settings/index.html.twig'))
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
            'icon' => 'bi bi-gear',
            'order' => 1,
        ];

        yield $this->createPage(self::PAGE_SETTINGS_DEFAULT)
            ->setController(AdminSettingsDefaultController::class)
            ->setTemplate($this->useTheme('/pages/admin/settings/default.html.twig'))
            ->addMenuItem(self::PAGE_SETTINGS_DEFAULT, $defaultItem, $this->settingsBatch);

        $emailItem = [
            'label' => 'Email',
            'href' => $this->urlGenerator('/' . self::PAGE_SETTINGS_EMAIL),
            'icon' => 'bi bi-envelope',
            'order' => 2,
        ];

        yield $this->createPage(self::PAGE_SETTINGS_EMAIL)
            ->setController(AdminSettingsDefaultController::class)
            ->setTemplate($this->useTheme('/pages/admin/settings/default.html.twig'))
            ->addMenuItem(self::PAGE_SETTINGS_EMAIL, $emailItem, $this->settingsBatch);

        $userItem = [
            'label' => 'Users',
            'href' => $this->urlGenerator('/' . self::PAGE_SETTINGS_USERS),
            'icon' => 'bi bi-person',
            'order' => 3,
        ];

        yield $this->createPage(self::PAGE_SETTINGS_USERS)
            ->setController(AdminSettingsDefaultController::class)
            ->setTemplate($this->useTheme('/pages/admin/settings/default.html.twig'))
            ->addMenuItem(self::PAGE_SETTINGS_USERS, $userItem, $this->settingsBatch);
    }

    protected function beforeRender(): void
    {
        $settingsNavigation = $this
            ->pageRepository
            ->getPageManager(self::PAGE_SETTINGS)
            ?->getMenuItem(self::PAGE_SETTINGS, true);

        $settingsBatchRegulator = new class($this->settingsBatch, $settingsNavigation) implements EventInterface
        {
            public function __construct(
                protected TreeNode $settingsBatch,
                protected ?TreeNode $settingsNavigation
            )
            {}
            
            public function eventAction(array|object $data): void
            {
                $this->orderBatch();
                $this->inspectActiveItem();
            }

            public function orderBatch(): void
            {
                $this->settingsBatch->sortChildren(function(TreeNode $a, TreeNode $b) {
                    return ($a->getAttr('order') ?? 0) <=> ($b->getAttr('order') ?? 0);
                });
            }

            public function inspectActiveItem(): void
            {
                if($this->settingsNavigation) {
                    foreach($this->settingsBatch->children as $treeNode) {
                        if($treeNode->getAttr('active')) {
                            $this->settingsNavigation->setAttr('active', true);
                            break;
                        }
                    }
                }
            }
        };

        (new Event())->addListener('dashboard:render', $settingsBatchRegulator, -10);
    }
}
