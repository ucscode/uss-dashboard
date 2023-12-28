<?php

namespace Module\Dashboard\Bundle\Kernel;

use Ucscode\TreeNode\TreeNode;
use Uss\Component\Event\Event;

abstract class AbstractDashboardCentral implements DashboardInterface
{
    public readonly AppControl $appControl;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;
    protected bool $firewallEnabled = true;
    protected array $documents = [];

    /**
     * Set Initial values such as base(route), theme, user permission etc
     * Note: child class should override this method but still call it
     * parent::createProject($config);
     */
    public function createApp(AppControl $appControl): void
    {
        $this->appControl = $appControl;
        AppFactory::registerApp($this);
        $this->menu = new TreeNode('MenuContainer');
        $this->userMenu = new TreeNode('UserMenuContainer');
        (new Event())->addListener('modules:loaded', fn () => $this->createGUI(), -10);
    }

    /**
     * Process the GUI/Theme for the created dashboard Application
     */
    private function createGUI(): void
    {
        $pageManagers = $this->pageRepository->getPageManagers();

        foreach($pageManagers as $pageManager) {
            foreach($pageManager->getMenuItems() as $name => $menuItem) {
                $menuItem['parent']->add($name, $menuItem['item']);
            };
        }

        $this->iterateMenu($this->menu, 1, 'Main Menu');
        $this->iterateMenu($this->userMenu, 2, 'User Menu');

        foreach($pageManagers as $pageManager) {
            $this->enablePageManager($pageManager);
        }
    }

    /**
     * @method iterateMenu
     */
    private function iterateMenu(TreeNode $menu, int $category, string $title): void
    {
        if(empty($menu->getAttr('label')) && !empty($menu->level)) {
            $exceptionMessage = sprintf(
                "%s: (Item: %s) must have a label attribute",
                $title,
                $menu->name
            );
            throw new \Exception($exceptionMessage);
        };

        if(!empty($menu->children)) {
            
            if($category === 1 && $menu->level && !is_null($menu->getAttr('href'))) {

                $label = $menu->getAttr('label') . '<i class="bi bi-pin-angle ms-1 menu-pin"></i>';
                
                $menu->add($menu->name, [
                    'label' => $label,
                    'href' => $menu->getAttr('href'),
                    'target' => $menu->getAttr('target') ?? '_self',
                    'order' => -1024,
                    'pinned' => true,
                    'active' => $menu->getAttr('active'),
                ]);
            }

            $menu->sortChildren(function ($a, $b) {
                $left = (int)($a->getAttr('order') ?? 0);
                $right = (int)($b->getAttr('order') ?? 0);
                return $left <=> $right;
            });

            foreach($menu->children as $childMenu) {
                $this->iterateMenu($childMenu, $category, $title);
            }
        }

        $this->defaultMenuAttributes($menu);
    }

    /**
     * @method defaultMenuAttributes
     */
    private function defaultMenuAttributes(TreeNode $menu): void
    {
        $attributes = [
            'target' => '_self',
            'href' => 'javascript:void(0)',
            'pinned' => false,
        ];

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
