<?php

use Ucscode\Event\Event;
use Ucscode\Packages\Pairs;

abstract class AbstractUdash
{
    use PropertyAccessTrait;

    // Abstract Child Methods

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

    public const DIR = __DIR__;

    public const VIEW_DIR = self::DIR . "/ud-view";

    public const RES_DIR = self::DIR . "/ud-resource";

    public const ASSETS_DIR = self::DIR . "/ud-assets";

    public const CLASS_DIR = self::RES_DIR . "/class";

    public const PAGES_DIR = self::DIR . "/ud-pages";


    // Default Class Variables

    #[Accessible]
    protected Pairs $usermeta;

    protected array $configs = [];

    private bool $initialized = false;

    // Inline Class Methods;

    public function init()
    {

        if($this->initialized) {
            return;
        }

        $uss = Uss::instance();

        if(!DB_ENABLED) {
            $uss->render('@Uss/error.html.twig', [
                "subject" => "Database Connection Disabled",
                "message" => "<span class='text-danger'>PROBLEM</span> &gt;&gt;&gt; " . highlight_string("define('DB_ENABLED', false)", true),
                "message_class" => "mb-5",
                "image" => $uss->getUrl(self::ASSETS_DIR . '/images/database-error-icon.webp'),
                "image_style" => "width: 150px"
            ]);
            return;
        };

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

            'templates:notification' => '@Udash/notification.html.twig',

            /**
             * This is the first page user will enter when they visit the dashboards
             * However, a login page will be displayed if user has no permission
             */
            'pages:index' => [
                'route' => $this->dashboardRoute(),
                'file' => self::PAGES_DIR . '/index.php',
                'template' => '@Udash/index.html.twig'
            ],

            'pages:register' => [
                'route' => $this->dashboardRoute() . '/register',
                'file' => self::PAGES_DIR . "/register.php",
                'template' => '@Udash/security/register.html.twig'
            ],

            'pages:recovery' => [
                'route' => $this->dashboardRoute() . '/reset',
                'file' => self::PAGES_DIR . "/recovery.php",
                'template' => '@Udash/security/recovery.html.twig',
            ],

            /*'pages:account' => [
                'route' => self::ROUTE . '/account',
                'file' => self::PAGES_DIR . '/account.php',
                'template' => '@Udash/account.html.twig'
            ],

            'pages:affiliate' => [
                'route' => self::ROUTE . '/affiliate',
                'file' => self::PAGES_DIR . '/affiliate.php',
                'template' => '@Udash/affiliate.html.twig'
            ],

            'pages:hierarchy' => [
                'route' => self::ROUTE . '/hierarchy',
                'file' => self::PAGES_DIR . '/hierarchy.php',
                'template' => '@Udash/hierarchy.html.twig'
            ],*/

        );

    }

    /**
     * Configure Udash
     */
    private function configureSystem(): void
    {
        require_once self::RES_DIR . "/declare-database.php";

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
        $uss->addTwigFilesystem(self::PAGES_DIR, 'Udash');

        $this->setConfig('forms:login', new UdashLoginForm('login'));
        $this->setConfig('forms:register', new UdashRegisterForm('register'));
        $this->setConfig('forms:recovery', new UdashRecoveryForm('recovery'));

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
    }

    private function dispatchEvent(): void
    {
        (new Event())->addListener('Modules:loaded', function () {

            // Inform all modules that Udash has started
            (new Event())->dispatch('Udash:OnStart');

            // Get all available pages
            $pages = $this->getConfig("pages:", true);

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

}
