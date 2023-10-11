<?php

use Ucscode\Packages\TreeNode;

final class Ud extends AbstractUd
{
    use SingletonTrait;

    public const DIR = self::SRC_DIR . '/Dashboard';
    public const TEMPLATE_DIR = self::DIR . "/templates";
    public const FORMS_DIR = self::DIR . '/forms';
    public const CONTROLLER_DIR = self::DIR . '/controllers';

    protected function createProject(): void 
    {
        $this->includeControllers();
        $this->registerComponents();
        $this->registerArchives();
        $this->configureJS();
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

            (new Archive(Archive::LOGIN))
            ->set('form', UdLoginForm::class)
            ->set('template', '@Ud/security/login.html.twig'),

            (new Archive('index'))
                ->set('route', '/')
                ->set('template', '@Ud/pages/welcome.html.twig')
                ->set('controller', IndexController::class)
                ->addMenuItem('index', new TreeNode('dashboard', [
                    'label' => 'dashboard',
                    'href' => $this->urlGenerator('/'),
                    'icon' => 'bi bi-speedometer',
                ]), $this->menu),

            (new Archive('register'))
            ->set('route', '/register')
            ->set('template', '@Ud/security/register.html.twig')
            ->set('controller', RegisterController::class)
            ->set('form', UdRegisterForm::class),

            (new Archive('recovery'))
                ->set('route', '/recovery')
                ->set('template', '@Ud/security/register.html.twig')
                ->set('controller', RecoveryController::class)
                ->set('form', UdRecoveryForm::class),

            (new Archive('notifications'))
                ->set('route', '/notifications')
                ->set('template', '@Ud/pages/notifications.html.twig')
                ->set('controller', NotificationController::class),

            (new Archive('logout'))
                ->set('route', '/logout')
                ->set('template', null)
                ->set('controller', LogoutController::class)
                ->setCustom('endpoint', $this->urlGenerator('/'))
                ->addMenuItem('logout', new TreeNode('logout', [
                    'label' => 'logout',
                    'href' => $this->urlGenerator('/logout'),
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
            'url' => $this->urlGenerator()->getResult(),
            'nonce' => $uss->nonce('Ud'),
            'loggedIn' => !!(new User())->getFromSession()
        ];

        $uss->addJsProperty('ud', $installment);
    }

}
