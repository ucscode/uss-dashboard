<?php

use Ucscode\Packages\Events;

abstract class AbstractUdash
{
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

    abstract protected function configureUser(): void;

    // Default Class Constants

    public const DIR = __DIR__;

    public const VIEW_DIR = self::DIR . "/ud-view";

    public const RES_DIR = self::DIR . "/ud-resource";

    public const ASSETS_DIR = self::DIR . "/ud-assets";

    public const CLASS_DIR = self::RES_DIR . "/class";

    public const PAGES_DIR = self::DIR . "/ud-pages";


    // Default Class Variables

    protected array $configs = [];

    private bool $initialized = false;

    // Inline Class Methods;

    public function init()
    {

        if($this->initialized) {
            return;
        }

        if(!DB_ENABLED) {
            Uss::instance()->render('@Uss/error.html.twig', [
                "subject" => "Database Connection Disabled",
                "message" => "<span class='text-danger'>PROBLEM</span> &gt;&gt;&gt; " . highlight_string("define('DB_ENABLED', false)", true),
                "message_class" => "mb-5",
                "image" => Core::url(self::ASSETS_DIR . '/images/database-error-icon.webp'),
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

        // Send Signal to alter other modules update any of the configuration
        // Then render the default pages
        $this->dispatchEvent();

        $this->initialized = true;

    }

    // Get the route declared by the child class

    private function getPrimeRoute(): string
    {
        $class = get_called_class();
        return constant("$class::ROUTE");
    }

    private function defaultConfigs(): void
    {

        $this->configs = array(

            'debug' => true,

            /**
             * templates:login is not considered a page because it is globally rendered irrespective of the route
             * I.E The login page will display by default on any page when a user tries to access the page
             * without sufficient permission
             */
            'templates:login' => '@Udash/security/login.html.twig',

            'templates:notification' => '@Udash/notification.html.twig',

            'pages:index' => [
                'route' => $this->getPrimeRoute(),
                'file' => self::PAGES_DIR . '/index.php',
                'template' => '@Udash/index.html.twig'
            ],

            'pages:register' => [
                'route' => $this->getPrimeRoute() . '/register',
                'file' => self::PAGES_DIR . "/register.php",
                'template' => '@Udash/security/register.html.twig'
            ],

            'pages:recovery' => [
                'route' => $this->getPrimeRoute() . '/reset',
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

        # Get global Config
        $options = Uss::instance()->options;

        # Setup Global Config
        foreach($defaultConfigs as $key => $value) {
            if(is_null($options->get($key))) {
                $options->set($key, $value);
            };
        };

        # Set Base Template Directory
        Uss::instance()->addTwigFilesystem(self::PAGES_DIR, 'Udash');

        $this->setConfig('forms:login', new UdashLoginForm('login'));
        $this->setConfig('forms:register', new UdashRegisterForm('register'));
        $this->setConfig('forms:recovery', new UdashRecoveryForm('recovery'));

    }

    private function dispatchEvent(): void
    {
        Events::instance()->addListener('modules:loaded', function () {

            // Inform all modules that Udash has started
            Events::instance()->exec('Udash:started');

            // Get all available pages
            $pages = $this->getConfig("pages:", true);

            foreach($pages as $index => $pageInfo) {
                
                if(is_array($pageInfo)) {

                    $pageInfo['_key'] = $index;

                    call_user_func(function () use ($pageInfo) {
                        
                        Events::instance()->exec('Udash:pageload', $pageInfo);
                       
                        require_once $pageInfo['file'];

                    });

                };

            }

            // Inform all modules that Udash has ended
            Events::instance()->exec('Udash:ended');

        });
    }

}
