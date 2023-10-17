<?php

use Ucscode\Packages\Pairs;
use Ucscode\Packages\TreeNode;

abstract class AbstractDashboardComposition implements DashboardInterface
{
    abstract protected function createProject(): void;
    abstract public function urlGenerator(string $path = '/', array $queries = []): UrlGenerator;

    public readonly string $base;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;
    public readonly Pairs $usermeta;
    public readonly ArchiveRepository $archiveRepository;

    protected bool $firewallEnabled = true;
    protected array $attributes = [
        'debug' => true
    ];
    protected array $dashboardJSProperty = [];

    private static bool $databaseInitialized = false;

    public function configureDashboard(string $base): void
    {
        $this->configureSetUp($base);

        if($this->databaseEnabled()) {

            if(!self::$databaseInitialized) {
                $this->configureDatabase();
                $this->configureDatabaseOptions();
                self::$databaseInitialized = true;
            }

            $this->configureUser();
            $this->configureProject();

        }
    }

    private function databaseEnabled(): bool
    {
        $uss = Uss::instance();

        if(!DB_ENABLED) {
            $message = [
                "subject" => "Database Connection Disabled",
                "message" => sprintf(
                    "<span class='%s'>PROBLEM</span> : define('DB_ENABLED', <span class='%s'>false</span>)",
                    'text-danger',
                    'text-primary'
                ),
                "message_class" => "mb-5",
                "image" => $uss->abspathToUrl(DashboardImmutable::ASSETS_DIR . '/images/database-error-icon.webp'),
                "image_style" => "width: 150px"
            ];
            $uss->render('@Uss/error.html.twig', $message);
        };

        return !!DB_ENABLED;
    }

    private function configureSetUp(string $base): void
    {
        $uss = Uss::instance();
        $this->base = $uss->filterContext($base);
        $this->archiveRepository = new ArchiveRepository($this::class);
    }

    private function configureDatabase(): void
    {
        $statements = [

            "CREATE TABLE IF NOT EXISTS %{prefix}users (
                id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(255) NOT NULL UNIQUE,
                username VARCHAR(25) DEFAULT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                register_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                usercode VARCHAR(12) NOT NULL UNIQUE,
                last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
                parent INT UNSIGNED DEFAULT NULL,
                FOREIGN KEY(parent) REFERENCES %{prefix}users(id) ON DELETE SET NULL
            )",

            "CREATE TABLE IF NOT EXISTS %{prefix}notifications (
                id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                origin INT,
                model VARCHAR(100) DEFAULT NULL COMMENT 'TYPE: Comment, Reply, Module-Name...',
                userid INT UNSIGNED NOT NULL,
                period TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                message VARCHAR(5000),
                viewed TINYINT NOT NULL DEFAULT 0,
                redirect VARCHAR(255) DEFAULT NULL COMMENT 'URL',
                image VARCHAR(255),
                hidden TINYINT NOT NULL DEFAULT 0,
                FOREIGN KEY(userid) REFERENCES %{prefix}users(id) ON DELETE CASCADE
            )"

        ];

        $uss = Uss::instance();

        foreach($statements as $SQL) {
            try {

                $SQL = $uss->replaceVar($SQL, ['prefix' => DB_PREFIX]);

                $result = $uss->mysqli->query($SQL);

                if(!$result) {
                    throw new \Exception($uss->mysqli->error);
                }

            } catch(\Exception $e) {

                $uss->render('@Uss/error.html.twig', [
                    "subject" => "Ud: Database Setup Error",
                    "message" => $this->getAttribute('debug') ? $e->getMessage() : 'MYSQL Error Number: ' . $uss->mysqli->errno
                ]);

                die();

            };
        };
    }

    private function configureDatabaseOptions(): void
    {
        $uss = Uss::instance();

        $configuration = [
            'user:disable-signup' => 0,
            'user:collect-username' => 0,
            'user:confirm-email' => 0,
            'user:lock-email' => 0,
            'user:reconfirm-email' => 1,
            'user:default-role' => 'member',
            'user:affiliation' => 0,
            'user:remove-inactive-after-day' => 7, // 0 or null to ignore
            'web:icon' => Uss::$globals['icon'],
            'web:title' => Uss::$globals['title'],
            'web:headline' => Uss::$globals['headline'],
            'web:description' => Uss::$globals['description'],
            'admin:email' => 'admin@example.com',
            'smtp:state' => 'default'
        ];

        foreach($configuration as $key => $value) {
            if(is_null($uss->options->get($key, null, true))) {
                $uss->options->set($key, $value);
            };
        };
    }

    private function configureUser(): void
    {
        $uss = Uss::instance();

        $this->usermeta = new Pairs($uss->mysqli, User::META_TABLE);
        $this->usermeta->linkParentTable([
            'parentTable' => User::TABLE,
        ]);

        $this->menu = new TreeNode('MenuContainer');
        $this->userMenu = new TreeNode('UserMenuContainer');
    }

    private function configureProject(): void
    {
        $uss = Uss::instance();

        if($this->isActiveBase()) {
            $uss->addTwigExtension(new DashboardTwigExtension($this));
        }
        
        $this->configureJS($uss);
        $this->createProject();
        $uss->addJsProperty('dashboard', $this->dashboardJSProperty);

        Event::instance()->addListener('modules:loaded', function () {
            $this->buildArchives();
            Event::instance()->emit('dashboard:render');
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
        $archiveRoute = $archive->get('route');

        if(!empty($archiveRoute) && $archive->name !== Archive::LOGIN) {
            $route = $uss->filterContext($this->base . "/" . $archiveRoute);
            $controller = $archive->get('controller');
            $method = $archive->get('method');
            new Route($route, new $controller($archive, $this), $method);
        }
    }

    private function configureJS(Uss $uss): void
    {
        $this->dashboardJSProperty = [
            'url' => $this->urlGenerator()->getResult(),
            'nonce' => $uss->nonce('Ud'),
            'loggedIn' => !!(new User())->getFromSession()
        ];
    }

}
