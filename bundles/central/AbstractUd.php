<?php

use Ucscode\Event\Event;
use Ucscode\Packages\Pairs;
use Ucscode\Packages\TreeNode;

abstract class AbstractUd
{
    abstract public function setConfig(string $property, mixed $value): bool;
    abstract public function getConfig(?string $property, bool $group): mixed;
    abstract public function removeConfig(string $property): void;
    abstract public function enableFirewall(bool $enable = true): void;
    abstract public function getPage(string $pageName): ?UdPage;
    abstract public function removePage(string $pageName): null|bool;
    abstract public function getPageUrl(string $pagename): ?string;
    abstract public function render(string $template, array $options = []): void;

    // Default Class Constants

    public const DIR = UD_DIR;
    public const ASSETS_DIR = self::DIR . "/assets";
    public const SRC_DIR = self::DIR . "/src";
    public const VIEW_DIR = self::DIR . "/view";
    public const RES_DIR = self::DIR . "/bundles";
    public const CENTRAL_DIR = self::RES_DIR . "/central";
    public const CLASS_DIR = self::RES_DIR . "/class";
    public const CONFIG_DIR = self::RES_DIR . "/configs";
    public const TEMPLATES_DIR = self::DIR . "/templates";

    // Default Class Variables

    public readonly Pairs $usermeta;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;

    protected array $configs = [];
    protected array $defaultPages = [];
    private bool $initialized = false;

    // Inline Class Methods;

    public function init()
    {
        if(!$this->initialized) {

            if($this->databaseEnabled()) {

                // Configure (create) database values, forms, and template directory
                $this->configureSystem();

                // Configure logged in user information and authentication
                $this->configureUser();

                // Initialize Ud with default configurations
                $this->configurePages();

                $this->initialized = true;

                // Send Signal to alter other modules update any of the configuration
                // Then render the default pages
                $this->dispatchEvent();

            }

        }
    }

    // Get the route declared by the child class

    private function mainRoute(): string
    {
        $class = get_called_class();
        return constant("$class::ROUTE");
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
            $uss->addTwigExtension(UdTwigExtension::class);
        }
        return !!DB_ENABLED;
    }

    /**
     * Configure Ud
     */
    private function configureSystem(): void
    {
        require_once self::CENTRAL_DIR . "/database.php";

        # Default Ud Configuration
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
        $uss->addTwigFilesystem(self::TEMPLATES_DIR, 'Ud');

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

        $this->menu = new TreeNode('MenuContainer');
        $this->userMenu = new TreeNode('UserMenuContainer');
    }

    /**
     * Create all default pages uses in Ud
     *
     * These pages can then be modified by modules:
     * The UdPage instance allows module to update single properties of the page such as
     * Controllers, Template, Route, MenuItems etc
     */
    private function configurePages(): void
    {
        $this->defaultPages = [

            (new UdPage(UdPage::LOGIN))
                ->set('template', '@Ud/security/login.html.twig')
                ->set('form', UdLoginForm::class),

            (new UdPage('index'))
                ->set('route', '/')
                ->set('template', '@Ud/pages/welcome.html.twig')
                ->set('controller', IndexController::class)
                ->addMenuItem('index', new TreeNode('dashboard', [
                    'label' => 'dashboard',
                    'href' => new UrlGenerator('/'),
                    'icon' => 'bi bi-speedometer',
                ]), $this->menu),

            (new UdPage('register'))
                ->set('route', '/register')
                ->set('template', '@Ud/security/register.html.twig')
                ->set('controller', RegisterController::class)
                ->set('form', UdRegisterForm::class),

            (new UdPage('recovery'))
                ->set('route', '/recovery')
                ->set('template', '@Ud/security/register.html.twig')
                ->set('controller', RecoveryController::class)
                ->set('form', UdRecoveryForm::class),

            (new UdPage('notifications'))
                ->set('route', '/notifications')
                ->set('template', '@Ud/pages/notifications.html.twig')
                ->set('controller', NotificationController::class),

            (new UdPage('logout'))
                ->set('route', '/logout')
                ->set('template', null)
                ->set('controller', LogoutController::class)
                ->setCustom('endpoint', new UrlGenerator('/'))
                ->addMenuItem('logout', new TreeNode('logout', [
                    'label' => 'logout',
                    'href' => new UrlGenerator('/logout'),
                    'icon' => 'bi bi-power',
                    'order' => 1024
                ]), $this->userMenu),

        ];

    }

    /**
     *
     */
    private function dispatchEvent(): void
    {
        Event::instance()->addListener('Modules:loaded', function () {

            // Inform all modules that Ud has started
            Event::instance()->dispatch('Ud:ready');

            $this->buildDefaultPages();

            // Inform all modules that Ud has ended
            Event::instance()->dispatch('Ud:ended');

        });
    }

    /**
     * Sort Ud Menu
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

    /**
     * Build Default Pages
     */
    private function buildDefaultPages(): void
    {
        $this->compileMenuItems();

        $this->recursiveMenuConfig($this->menu, 'Main Menu');
        $this->recursiveMenuConfig($this->userMenu, 'User Menu');

        foreach($this->defaultPages as $singlePage) {
            $this->activateDefaultPage($singlePage);
        }
    }

    private function compileMenuItems(): void
    {
        foreach($this->defaultPages as $page) {
            foreach($page->getMenuItems() as $name => $menuItem) {
                $item = $menuItem['item'];
                $menuItem['parent']->add($name, $item);
            };
        }
    }

    private function activateDefaultPage(UdPage $page): void
    {
        $uss = Uss::instance();

        $pageRoute = $page->get('route');
        
        if(empty($pageRoute) || $page->name === UdPage::LOGIN) {
            return;
        };
        
        $route = $this->mainRoute() . "/" . $pageRoute;
        $route = $uss->filterContext($route);

        $controller = $page->get('controller');
        $method = $page->get('method');
        
        new Route($route, new $controller($page), $method);
    }

}
