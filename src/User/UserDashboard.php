<?php

use Ucscode\Packages\TreeNode;

class UserDashboard extends AbstractDashboard
{
    use SingletonTrait;

    public const DIR = DashboardImmutable::SRC_DIR . '/User';

    public const FORMS_DIR = self::DIR . '/forms';
    public const TEMPLATE_DIR = self::DIR . "/templates";
    public const CONTROLLER_DIR = self::DIR . '/controllers';

    protected function createProject(): void
    {
        $uss = Uss::instance();
        $uss->addTwigFilesystem(self::TEMPLATE_DIR, 'Ud');
        $this->includeControllers();
        $this->registerArchives();
    }

    protected function includeControllers(): void
    {
        $projectFile = [

            self::FORMS_DIR => [
                "UserLoginForm.php",
                "UserRegisterForm.php",
                "UserRecoveryForm.php",
            ],

            self::CONTROLLER_DIR => [
                //'LoginController.php',
                'UserRegisterController.php',
                'UserRecoveryController.php',
                'UserIndexController.php',
                'UserLogoutController.php',
                'UserNotificationController.php',
            ]

        ];

        foreach($projectFile as $directory => $files) {
            foreach($files as $filename) {
                require_once $directory . '/' . $filename;
            }
        }
    }

    protected function registerArchives(): void
    {
        $archives = [

            (new Archive(Archive::LOGIN))
            ->set('form', UserLoginForm::class)
            ->set('template', '@Ud/security/login.html.twig'),

            (new Archive('index'))
                ->set('route', '/')
                ->set('template', '@Ud/pages/welcome.html.twig')
                ->set('controller', UserIndexController::class)
                ->addMenuItem('index', new TreeNode('dashboard', [
                    'label' => 'dashboard',
                    'href' => $this->urlGenerator('/'),
                    'icon' => 'bi bi-speedometer',
                ]), $this->menu),

            (new Archive('register'))
            ->set('route', '/register')
            ->set('template', '@Ud/security/register.html.twig')
            ->set('controller', UserRegisterController::class)
            ->set('form', UserRegisterForm::class),

            (new Archive('recovery'))
                ->set('route', '/recovery')
                ->set('template', '@Ud/security/register.html.twig')
                ->set('controller', UserRecoveryController::class)
                ->set('form', UserRecoveryForm::class),

            (new Archive('notifications'))
                ->set('route', '/notifications')
                ->set('template', '@Ud/pages/notifications.html.twig')
                ->set('controller', UserNotificationController::class),

            (new Archive('logout'))
                ->set('route', '/logout')
                ->set('template', null)
                ->set('controller', UserLogoutController::class)
                ->setCustom('endpoint', $this->urlGenerator('/'))
                ->addMenuItem('logout', new TreeNode('logout', [
                    'label' => 'logout',
                    'href' => $this->urlGenerator('/logout'),
                    'icon' => 'bi bi-power',
                    'order' => 1024
                ]), $this->userMenu),

        ];

        $ar = new ArchiveRepository($this::class);

        foreach($archives as $archive) {
            $ar->addArchive($archive->name, $archive);
        };

    }

}
