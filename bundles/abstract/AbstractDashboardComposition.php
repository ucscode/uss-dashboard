<?php

use Ucscode\TreeNode\TreeNode;

abstract class AbstractDashboardComposition implements DashboardInterface
{
    public readonly DashboardConfig $config;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;
    public readonly PageRepository $pageRepository;
    protected bool $firewallEnabled = true;
    protected array $attributes = [];

    /**
     * Set Initial values such as base(route), theme, user permission etc
     * Note: child class should override this method but still call it
     * parent::createProject($config);
     */
    public function createProject(DashboardConfig $config): void
    {
        $this->config = $config;
        $this->pageRepository = new PageRepository($this::class);
        $this->menu = new TreeNode('MenuContainer');
        $this->userMenu = new TreeNode('UserMenuContainer');
        (new Event())->addListener('modules:loaded', fn () => $this->buildPages(), -10);
    }

    /**
     * @method buildPages
     */
    private function buildPages(): void
    {
        $pageManagers = $this->pageRepository->getPageManagers();
        $this->extractMenuItems($pageManagers);
        $this->configureMenuRecursively($this->menu, 'Main Menu');
        $this->configureMenuRecursively($this->userMenu, 'User Menu');
        foreach($pageManagers as $pageManager) {
            $this->enablePageManager($pageManager);
        }
    }

    /**
     * @method extractMenuItems
     */
    private function extractMenuItems(array $pageManagers): void
    {
        foreach($pageManagers as $pageManager) {
            foreach($pageManager->getMenuItem() as $name => $menuItem) {
                $item = $menuItem['item'];
                $menuItem['parent']->add($name, $item);
            };
        }
    }

    /**
     * @method configureMenuRecursively
     */
    private function configureMenuRecursively(TreeNode $menu, string $title): void
    {
        if(empty($menu->getAttr('label')) && !empty($menu->level)) {
            throw new \Exception(
                sprintf(
                    "%s: (Item: %s) must have a label attribute",
                    $title,
                    $menu->name
                )
            );
        };

        $menu->sortChildren(function ($a, $b) {
            $left = (int)($a->getAttr('order') ?? 0);
            $right = (int)($b->getAttr('order') ?? 0);
            return $left <=> $right;
        });

        $defaultAttr = [
            'target' => '_self',
            'href' => 'javascript:void(0)'
        ];

        $this->setDefaultMenuAttributes($menu, $defaultAttr);

        if(!empty($menu->children)) {
            $this->setDefaultMenuAttributes($menu, $defaultAttr);
            foreach($menu->children as $childMenu) {
                $this->configureMenuRecursively($childMenu, $title);
            }
        }

        if($menu->getAttr('active')) {
            $parentNode = $menu->parentNode;
            while($parentNode && $parentNode->level) {
                $parentNode->setAttr('isExpanded', true);
                $parentNode = $parentNode->parentNode;
            }
        }
    }

    /**
     * @method setDefaultMenuAttributes
     */
    private function setDefaultMenuAttributes(TreeNode $menu, array $attributes): void
    {
        foreach($attributes as $key => $value) {
            if(empty($menu->getAttr($key))) {
                $menu->setAttr($key, $value);
            };
        }
    }

    /**
     * @method enablePageManager
     */
    private function enablePageManager(PageManager $pageManager): void
    {
        $pageManagerRoute = $pageManager->getRoute();
        if(!empty($pageManagerRoute) && $pageManager->name !== PageManager::LOGIN) {
            $route = Uss::instance()->filterContext($this->config->getBase() . "/" . $pageManagerRoute);
            $controller = $pageManager->getController();
            $method = $pageManager->getRequestMethods();
            new Route($route, new $controller($pageManager, $this), $method);
        }
    }
}
