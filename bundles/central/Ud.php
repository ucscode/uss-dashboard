<?php

use Ucscode\Packages\TreeNode;

final class Ud extends AbstractUd
{
    use SingletonTrait;

    public const DIR = self::SRC_DIR . '/Dashboard';
    public const TEMPLATE_DIR = self::DIR . "/templates";
    public const FORMS_DIR = self::DIR . '/forms';
    public const CONTROLLER_DIR = self::DIR . '/controllers';

    public function createProject(array $config): void
    {
        parent::createProject($config);

        $this->includeControllers();
        $this->registerComponents();
        $this->registerArchives();
        $this->configureJS();

        parent::emitEvents();
    }

    protected function includeControllers(): void
    {
        $projectFile = [

            self::DIR => [
                'UdTwigExtension.php',
            ],

            self::FORMS_DIR => [
                "UdLoginForm.php",
                "UdRegisterForm.php",
                "UdRecoveryForm.php",
            ],

            self::CONTROLLER_DIR => [
                //'LoginController.php',
                'RegisterController.php',
                'RecoveryController.php',
                'IndexController.php',
                'LogoutController.php',
                'NotificationController.php',
            ]

        ];

        foreach($projectFile as $directory => $files) {
            foreach($files as $filename) {
                require_once $directory . '/' . $filename;
            }
        }
    }

    protected function registerComponents(): void
    {
        Uss::instance()->addTwigExtension(UdTwigExtension::class);
    }

    protected function registerArchives(): void
    {
        $archives = [

            (new UdArchive('login'))
            ->set('form', UdLoginForm::class)
            ->set('template', '@Ud/security/login.html.twig'),

            (new UdArchive('index'))
                ->set('route', '/')
                ->set('template', '@Ud/pages/welcome.html.twig')
                ->set('controller', IndexController::class)
                ->addMenuItem('index', new TreeNode('dashboard', [
                    'label' => 'dashboard',
                    'href' => new UrlGenerator('/'),
                    'icon' => 'bi bi-speedometer',
                ]), $this->menu),

            (new UdArchive('register'))
            ->set('route', '/register')
            ->set('template', '@Ud/security/register.html.twig')
            ->set('controller', RegisterController::class)
            ->set('form', UdRegisterForm::class),

            (new UdArchive('recovery'))
                ->set('route', '/recovery')
                ->set('template', '@Ud/security/register.html.twig')
                ->set('controller', RecoveryController::class)
                ->set('form', UdRecoveryForm::class),

            (new UdArchive('notifications'))
                ->set('route', '/notifications')
                ->set('template', '@Ud/pages/notifications.html.twig')
                ->set('controller', NotificationController::class),

            (new UdArchive('logout'))
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

        foreach($archives as $archive) {
            $this->addArchive($archive);
        };

    }

    protected function configureJS(): void
    {
        $uss = Uss::instance();

        $installment = [
            'url' => (new UrlGenerator())->getResult(),
            'nonce' => $uss->nonce('Ud'),
            'loggedIn' => !!(new User())->getFromSession()
        ];

        $uss->addJsProperty('ud', $installment);
    }

    /**
     * Activate login page
     *
     * Login page do not need controller or route.
     * The login page will automatically appear on any route once the user is not authorized
     * Unless the firewall is disabled before the render method is called
     */
    private function activateLoginPage(User &$user, string &$template, array &$options): void
    {
        $loginPage = $this->getArchive(UdArchive::LOGIN);

        // Get login form and handles submission
        $options['form'] = new ($loginPage->get('form'))(UdArchive::LOGIN);
        $options['form']->handleSubmission();

        // After form submission has been handled, checks again if user is authorized
        if(!$user->getFromSession()) {
            // If not, display login page
            $template = $loginPage->get('template');
        }
    }

}
