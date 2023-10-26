<?php

use Ucscode\Packages\TreeNode;

class AdminDashboard extends AbstractDashboard
{
    use SingletonTrait;

    public const DIR = DashboardImmutable::SRC_DIR . '/Admin';

    public const FORM_DIR = self::DIR . '/forms';
    public const TEMPLATE_DIR = self::DIR . '/templates';
    public const CONTROLLER_DIR = self::DIR . '/controllers';
    public const ASSETS_DIR = self::DIR . '/assets';

    public function createProject(DashboardConfig $config): void
    {
        parent::createProject($config);
        $uss = Uss::instance();
        $this->includeControllers();
        $this->registerArchives();
    }

    protected function includeControllers()
    {
        $source = [
            self::FORM_DIR => [
                'AdminLoginForm.php',
            ],
            self::CONTROLLER_DIR => [
                'AdminIndexController.php',
                'AdminUserController.php',
            ]
        ];

        foreach($source as $path => $files) {
            foreach($files as $file) {
                $controller = $path . '/' . $file;
                require_once $controller;
            }
        }

    }

    protected function registerArchives(): void
    {
        $archives = $this->createArchives();
        foreach($archives as $archive) {
            $this->archiveRepository->addArchive($archive->name, $archive);
        }
    }

    protected function createArchives(): iterable
    {
        $dashboard = UserDashboard::instance();

        yield (new Archive(Archive::LOGIN))
            ->setForm(AdminLoginForm::class)
            ->setTemplate($this->useTheme('/pages/admin/security/login.html.twig'));

        yield (new Archive('index'))
            ->setController(AdminIndexController::class)
            ->setRoute('/')
            ->setTemplate($this->useTheme('/pages/admin/index.html.twig'))
            ->addMenuItem('index', [
                'label' => 'Dashboard',
                'icon' => 'bi bi-microsoft',
                'href' => $this->urlGenerator()
            ], $this->menu);

        yield (new Archive('logout'))
            ->setController(UserLogoutController::class)
            ->setRoute('/logout')
            ->addMenuItem('logout', [
                'icon' => 'bi bi-power',
                'order' => 1024,
                'label' => 'logout',
                'href' => $this->urlGenerator('/logout')
            ], $this->userMenu)
            ->setCustom('endpoint', $this->urlGenerator());

        yield (new Archive('notifications'))
            ->setRoute('/notifications')
            ->setController(UserNotificationController::class)
            ->setTemplate($this->useTheme('/pages/notifications.html.twig'));

        yield (new Archive('users'))
            ->setRoute('/users')
            ->setController(AdminUserController::class)
            ->setTemplate($this->useTheme('/pages/admin/users.html.twig'))
            ->addMenuItem('users', $this->createUserMenuItem(), $this->menu);
    }

    protected function createUserMenuItem(): TreeNode
    {
        $parentItem = new TreeNode('users', [
            'label' => 'Users',
            'icon' => 'bi bi-people-fill',
            'href' => $this->urlGenerator('/users'),
        ]);
        return $parentItem;
    }
}
