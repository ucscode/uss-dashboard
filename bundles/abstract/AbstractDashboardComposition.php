<?php

use Ucscode\Packages\TreeNode;

abstract class AbstractDashboardComposition implements DashboardInterface
{
    abstract protected function createProject(): void;
    abstract public function urlGenerator(string $path = '/', array $queries = []): UrlGenerator;

    public readonly DashboardConfig $config;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;
    public readonly ArchiveRepository $archiveRepository;
    protected bool $firewallEnabled = true;
    protected array $attributes = [];

    public function configureDashboard(DashboardConfig $config): void
    {
        $this->config = $config;
        $this->archiveRepository = new ArchiveRepository($this::class);
        $this->configureUser();
        $this->configureProject();
    }

    private function configureUser(): void
    {
        User::establish();
        $this->menu = new TreeNode('MenuContainer');
        $this->userMenu = new TreeNode('UserMenuContainer');
    }

    private function configureProject(): void
    {
        $uss = Uss::instance();

        if($this->isActiveBase()) {
            $this->configureJS($uss);
        }

        $this->createProject();

        Event::instance()->addListener('modules:loaded', function () {
            $this->buildArchives();
        }, -10);
    }

    private function buildArchives(): void
    {
        $archives = $this->archiveRepository->getAllArchives();

        $this->compileMenuItems($archives);
        $this->recursiveMenuConfig($this->menu, 'Main Menu');
        $this->recursiveMenuConfig($this->userMenu, 'User Menu');

        foreach($archives as $archive) {
            $this->configureArchive($archive);
        }
    }

    private function compileMenuItems(array $archives): void
    {
        foreach($archives as $archive) {
            foreach($archive->getMenuItem() as $name => $menuItem) {
                $item = $menuItem['item'];
                $menuItem['parent']->add($name, $item);
            };
        }
    }

    private function recursiveMenuConfig(TreeNode $menu, string $title): void
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
            $leftOrder = (int)($a->getAttr('order') ?? 0);
            $rightOrder = (int)($b->getAttr('order') ?? 0);
            return $leftOrder <=> $rightOrder;
        });

        $defaultAttr = [
            'target' => '_self',
            'href' => 'javascript:void(0)'
        ];

        $this->presetMenuAttribute($menu, $defaultAttr);

        if(!empty($menu->children)) {
            $this->presetMenuAttribute($menu, $defaultAttr);
            foreach($menu->children as $childMenu) {
                $this->recursiveMenuConfig($childMenu, $title);
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

    private function presetMenuAttribute(TreeNode $menu, array $attributes): void
    {
        foreach($attributes as $key => $value) {
            if(empty($menu->getAttr($key))) {
                $menu->setAttr($key, $value);
            };
        }
    }

    private function configureArchive(Archive $archive): void
    {
        $uss = Uss::instance();
        $archiveRoute = $archive->getRoute();

        if(!empty($archiveRoute) && $archive->name !== Archive::LOGIN) {
            $route = $uss->filterContext($this->config->base . "/" . $archiveRoute);
            $controller = $archive->getController();
            $method = $archive->getRequestMethods();
            new Route($route, new $controller($archive, $this), $method);
        }
    }

    private function configureJS(Uss $uss): void
    {
        $uss->addJsProperty('dashboard', [
            'url' => $this->urlGenerator()->getResult(),
            'nonce' => $uss->nonce('Ud'),
            'loggedIn' => !!(new User())->getFromSession()
        ]);
    }
}
