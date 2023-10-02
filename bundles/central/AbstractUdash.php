<?php

use Ucscode\Event\Event;
use Ucscode\Packages\Pairs;
use Ucscode\Packages\TreeNode;

abstract class AbstractUdash
{
    abstract public function setConfig(string $property, mixed $value): bool;
    abstract public function getConfig(?string $property, bool $group): mixed;
    abstract public function removeConfig(string $property): void;
    abstract public function enableFirewall(bool $enable = true): void;
    abstract public function render(
        string $template,
        array $options = [],
        ?UssTwigBlockManager $ussTwigBlockManager = null
    ): void;

    // Default Class Constants

    public const DIR = UD_DIR;
    public const ASSETS_DIR = self::DIR . "/assets";
    public const SRC_DIR = self::DIR . "/src";
    public const VIEW_DIR = self::DIR . "/view";
    public const RES_DIR = self::DIR . "/bundles";
    public const CENTRAL_DIR = self::RES_DIR . "/central";
    public const CLASS_DIR = self::RES_DIR . "/class";
    public const TEMPLATES_DIR = self::DIR . "/templates";

    // Default Class Variables

    public readonly Pairs $usermeta;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;
    protected array $configs;
    private bool $initialized = false;

    // Inline Class Methods;

    public function init()
    {
        if(!$this->initialized) {

            if($this->databaseEnabled()) {

                // Initialize Udash with default configurations
                $this->defaultConfigs();

                // Configure (create) database values, forms, and template directory
                $this->configureSystem();

                // Configure logged in user information and authentication
                $this->configureUser();

                $this->initialized = true;

                // Send Signal to alter other modules update any of the configuration
                // Then render the default pages
                $this->dispatchEvent();

            }

        }
    }

    // Get the route declared by the child class

    private function dashboardRoute(): string
    {
        $class = get_called_class();
        return constant("$class::ROUTE");
    }

    private function defaultConfigs(): void
    {

        $this->configs = array(

            'debug' => true,

            /**
             * HINTS:
             * - There is no login page file, there is only a "login template" and "login form class"
             * - The login template will render by default on any page when a user tries to access the page without sufficient permission
             * - The "handleSubmission()" method is called within the "login form class" itself
             */
            'templates:login' => '@Udash/security/login.html.twig',

            /**
             * This is the first page user will enter when they visit the dashboards
             * However, a login page will be displayed if user has no permission
             */
            'pages:index' => [
                'route' => $this->dashboardRoute(),
                'file' => self::SRC_DIR . '/index.php',
                'template' => '@Udash/pages/welcome.html.twig',
                'menu-item' => [
                    'category' => 'menu',
                    'name' => 'index',
                    'item' => [
                        'label' => 'dashboard',
                        'href' => new UrlGenerator(),
                        'icon' => 'bi bi-speedometer',
                    ]
                ]
            ],

            'pages:register' => [
                'route' => $this->dashboardRoute() . '/register',
                'file' => self::SRC_DIR . "/register.php",
                'template' => '@Udash/security/register.html.twig'
            ],

            'pages:recovery' => [
                'route' => $this->dashboardRoute() . '/reset',
                'file' => self::SRC_DIR . "/recovery.php",
                'template' => '@Udash/security/recovery.html.twig',
            ],

            'pages:logout' => [
                'route' => $this->dashboardRoute() . '/logout',
                'file' => self::SRC_DIR . "/logout.php",
                'template' => null,
                'menu-item' => [
                    'category' => 'user-menu',
                    'name' => 'logout',
                    'item' => [
                        'label' => 'logout',
                        'href' => new UrlGenerator('/logout'),
                        'icon' => 'bi bi-power',
                        'order' => 1024
                    ]
                ],
                'endpoint' => new UrlGenerator('/')
            ],

            'pages:notifications' => [
                'route' => $this->dashboardRoute() . '/notifications',
                'file' => self::SRC_DIR . "/notifications.php",
                'template' => '@Udash/pages/notifications.html.twig',
            ]

            /*'pages:account' => [
                'route' => self::ROUTE . '/account',
                'file' => self::SRC_DIR . '/account.php',
                'template' => '@Udash/account.html.twig'
            ],

            'pages:affiliate' => [
                'route' => self::ROUTE . '/affiliate',
                'file' => self::SRC_DIR . '/affiliate.php',
                'template' => '@Udash/affiliate.html.twig'
            ],

            'pages:hierarchy' => [
                'route' => self::ROUTE . '/hierarchy',
                'file' => self::SRC_DIR . '/hierarchy.php',
                'template' => '@Udash/hierarchy.html.twig'
            ],*/

        );

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
                "image" => $uss->getUrl(self::ASSETS_DIR . '/images/database-error-icon.webp'),
                "image_style" => "width: 150px"
            ]);
        } else {
            $uss->addTwigExtension(UdashTwigExtension::class);
        }
        return !!DB_ENABLED;
    }

    /**
     * Configure Udash
     */
    private function configureSystem(): void
    {
        require_once self::CENTRAL_DIR . "/database.php";

        # Default Udash Configuration
        $defaultConfigs = [
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

        $uss = Uss::instance();

        # Setup Global Config
        foreach($defaultConfigs as $key => $value) {
            if(is_null($uss->options->get($key))) {
                $uss->options->set($key, $value);
            };
        };

        # Set Base Template Directory
        $uss->addTwigFilesystem(self::TEMPLATES_DIR, 'Udash');

    }

    /**
     * Configure the user settings.
     *
     * @return void
     */
    private function configureUser(): void
    {
        $uss = Uss::instance();
        $this->usermeta = new Pairs($uss->mysqli, User::META_TABLE);
        $this->usermeta->linkParentTable([
            'parentTable' => User::TABLE,
        ]);

        $this->setConfig('forms:login', new UdashLoginForm('login'));
        $this->setConfig('forms:register', new UdashRegisterForm('register'));
        $this->setConfig('forms:recovery', new UdashRecoveryForm('recovery'));

        $this->menu = new TreeNode('MenuContainer');
        $this->userMenu = new TreeNode('UserMenuContainer');
    }

    /**
     *
     */
    private function dispatchEvent(): void
    {
        (new Event())->addListener('Modules:loaded', function () {

            // Inform all modules that Udash has started
            (new Event())->dispatch('Udash:OnStart');

            // Get all available pages
            $pages = $this->getConfig("pages:", true);

            // Parse Menu
            $this->parseMenuItems($pages);

            // Sort Menu Based On Order Attribute
            $this->recursiveMenuConfig($this->menu, 'Main Menu');
            $this->recursiveMenuConfig($this->userMenu, 'User Menu');

            foreach($pages as $index => $pageInfo) {

                if(is_array($pageInfo)) {

                    $pageInfo['_key'] = $index;

                    call_user_func(function () use ($pageInfo) {

                        (new Event())->dispatch('Udash:OnPageload', $pageInfo);

                        require_once $pageInfo['file'];

                    });

                };

            }

            // Inform all modules that Udash has ended
            (new Event())->dispatch('Udash:OnEnd');

        });
    }

    /**
     * Sort Udash Menu
     * All menu children will be sorted according to the "order" attribute
     */
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

    private function parseMenuItems(array $pages): void
    {
        foreach($pages as $key => $page) {

            $menuItem = $page['menu-item'] ?? null;
            $item = $menuItem['item'] ?? null;

            if(is_array($menuItem) && is_array($item)) {
                $category = $this->getMenuCategory($key, strtolower($menuItem['category'] ?? null));

                if(empty($menuItem['name'])) {
                    $this->throwConfigException(
                        "Menu item `name` must be defined for key '{$key}'"
                    );
                };

                $menuTree = $category == 'menu' ? $this->menu : $this->userMenu;

                $menuTree->add($menuItem['name'], $item);

            }

        }
    }

    private function getMenuCategory(string $key, string $category): string
    {
        $error = null;
        $categories = ['menu', 'user-menu'];

        if(empty($category)) {
            $error = "Menu item `category` must be defined for key '{$key}'";
        } elseif(!in_array($category, $categories)) {
            $error = sprintf(
                'Menu item `category` "%s" for key "%s" must be subset of [%s]',
                $category,
                $key,
                implode(", ", $categories)
            );
        };

        if(!empty($error)) {
            $this->throwConfigException($error);
        }

        return $category;
    }

    private function throwConfigException(string $error): void
    {
        throw new Exception(
            sprintf(
                "%s configuration error: %s",
                get_called_class(),
                $error
            )
        );
    }

}
