<?php

use Ucscode\Packages\Pairs;
use Ucscode\Packages\TreeNode;

abstract class AbstractDashboardComposition implements DashboardInterface
{
    abstract protected function createProject(): void;

    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;
    public readonly Pairs $usermeta;
    public readonly DashboardConfig $config;
    public readonly ArchiveRepository $archiveRepository;

    protected bool $firewallEnabled = true;
    protected array $attributes = [
        'debug' => true
    ];

    public function configureDashboard(DashboardConfig $config): void
    {
        if($this->databaseEnabled()) {
            $this->configureSetUp($config);
            $this->configureDatabase();
            $this->configureDatabaseOptions();
            $this->configureUser();
            $this->isolateProject();
        }
    }

    private function databaseEnabled(): bool
    {
        $uss = Uss::instance();

        if(!DB_ENABLED) {
            $uss->render('@Uss/error.html.twig', [
                "subject" => "Database Connection Disabled",
                "message" => sprintf(
                    "<span class='%s'>PROBLEM</span> : define('DB_ENABLED', <span class='%s'>false</span>)",
                    'text-danger',
                    'text-primary'
                ),
                "message_class" => "mb-5",
                "image" => $uss->abspathToUrl(DashboardImmutable::ASSETS_DIR . '/images/database-error-icon.webp'),
                "image_style" => "width: 150px"
            ]);
        };

        return !!DB_ENABLED;
    }

    private function configureSetUp(DashboardConfig $config): void
    {
        $uss = Uss::instance();
        $uss->addTwigFilesystem($config->getTemplateDirectory(), $config->getTemplateNamespace());
        $this->config = $config;
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

    private function isolateProject(): void
    {
        $this->createProject();
        $this->buildArchives();
    }

    private function buildArchives(): void
    {
        $this->compileMenuItems();
        $this->recursiveMenuConfig($this->menu, 'Main Menu');
        $this->recursiveMenuConfig($this->userMenu, 'User Menu');

        $archives = $this->archiveRepository->getAllArchives();
        foreach($archives as $singlePage) {
            $this->activateDefaultPage($singlePage);
        }
    }

    private function compileMenuItems(): void
    {
        $archives = $this->archiveRepository->getAllArchives();
        foreach($archives as $page) {
            foreach($page->getMenuItems() as $name => $menuItem) {
                $item = $menuItem['item'];
                $menuItem['parent']->add($name, $item);
            };
        }
    }

    private function recursiveMenuConfig(TreeNode $menu, string $title): void
    {
        if(empty($menu->getAttr('label')) && !empty($menu->level)) {
            $name = $menu->name;
            throw new \Exception("{$title}: (Item: {$name}) must have a label attribute");
        };

        $menu->sortChildren(function ($a, $b) {
            $aOrder = (int)($a->getAttr('order') ?? 0);
            $bOrder = (int)($b->getAttr('order') ?? 0);
            return $aOrder <=> $bOrder;
        });

        if(empty($menu->getAttr('target'))) {
            $menu->setAttr('target', '_self');
        };

        if(empty($menu->getAttr('href'))) {
            $menu->setAttr('href', 'javascript:void(0)');
        };

        if(!empty($menu->children)) {
            $menu->setAttr('href', 'javascript:void(0)');
            $menu->setAttr('target', '_self');
            foreach($menu->children as $childMenu) {
                $this->recursiveMenuConfig($childMenu, $title);
            }
        }

        if($menu->getAttr('active')) {
            $parentNode = $menu->parentNode;
            $x = 0;
            while($parentNode && $parentNode->level) {
                $parentNode->setAttr('isExpanded', true);
                $parentNode = $parentNode->parentNode;
            }
        }

    }

    private function activateDefaultPage(Archive $archive): void
    {
        $uss = Uss::instance();

        $archiveRoute = $archive->get('route');

        if(empty($archiveRoute) || $archive->name === Archive::LOGIN) {
            return;
        };

        $fullRoute = $uss->filterContext($this->config->getBase() . "/" . $archiveRoute);

        $controller = $archive->get('controller');
        $method = $archive->get('method');

        new Route($fullRoute, new $controller($archive), $method);
    }

}
