<?php

use Ucscode\Packages\TreeNode;

abstract class AbstractDashboardComposition implements DashboardInterface
{
    public readonly DashboardConfig $config;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;
    public readonly ArchiveRepository $archiveRepository;
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
        $this->archiveRepository = new ArchiveRepository($this::class);
        $this->menu = new TreeNode('MenuContainer');
        $this->userMenu = new TreeNode('UserMenuContainer');
        (new Event())->addListener('modules:loaded', fn () => $this->buildArchives(), -10);
    }

    /**
     * @method buildArchives
     */
    private function buildArchives(): void
    {
        $archives = $this->archiveRepository->getAllArchives();
        $this->extractMenuItems($archives);
        $this->configureMenuRecursively($this->menu, 'Main Menu');
        $this->configureMenuRecursively($this->userMenu, 'User Menu');
        foreach($archives as $archive) {
            $this->enableArchive($archive);
        }
    }

    /**
     * @method extractMenuItems
     */
    private function extractMenuItems(array $archives): void
    {
        foreach($archives as $archive) {
            foreach($archive->getMenuItem() as $name => $menuItem) {
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
     * @method enableArchive
     */
    private function enableArchive(Archive $archive): void
    {
        $archiveRoute = $archive->getRoute();
        if(!empty($archiveRoute) && $archive->name !== Archive::LOGIN) {
            $route = Uss::instance()->filterContext($this->config->getBase() . "/" . $archiveRoute);
            $controller = $archive->getController();
            $method = $archive->getRequestMethods();
            new Route($route, new $controller($archive, $this), $method);
        }
    }
}
