<?php

use Ucscode\TreeNode\TreeNode;

class UserDashboard extends AbstractDashboard
{
    use SingletonTrait;

    public const DIR = DashboardImmutable::SRC_DIR . '/User';
    public const ASSETS_DIR = self::DIR . '/assets';
    public const FORMS_DIR = self::DIR . '/forms';
    public const CONTROLLER_DIR = self::DIR . '/controllers';
    public const TEMPLATE_DIR = self::DIR . "/templates";

    public readonly TreeNode $profileMenu;

    /**
     * This is the entry method for any class that extends AbstractDashboard
     * @method main
     */
    public function createProject(DashboardConfig $config): void
    {
        parent::createProject($config);
        $this->profileMenu = new TreeNode('profileMenu');
        $this->getUserControllers();
        $this->registerArchives();
        $this->beforeRender();
    }

    /**
     * @method getUserControllers
     */
    protected function getUserControllers(): void
    {
        $projectFile = $this->getControllerCollections();

        foreach($projectFile as $dir => $controllerFiles) {
            foreach($controllerFiles as $filename) {
                require_once $dir . '/' . $filename;
            };
        }
    }

    /**
     * @method registerArchives
     */
    protected function registerArchives(): void
    {
        $archiveCollection = [
            $this->getAuthArchives(),
            $this->getPageArchives()
        ];

        foreach($archiveCollection as $archives) {
            foreach($archives as $archive) {
                $this->archiveRepository->addArchive($archive->name, $archive);
            }
        };

    }

    /**
     * @method beforeRender
     */
    protected function beforeRender(): void
    {
        (new Event())->addListener('dashboard:render', function () {
            foreach($this->profileMenu->children as $child) {
                if($child->getAttr('active')) {
                    $profileMenu = $this->archiveRepository->getArchive('profile')?->getMenuItem('profile', true);
                    if($profileMenu) {
                        $profileMenu->setAttr('active', true);
                    }
                }
            };
        }, -10);
    }

    /**
     * @method getControllerCollections
     */
    private function getControllerCollections(): array
    {
        return [
            self::FORMS_DIR => [
                "UserLoginForm.php",
                "UserRegisterForm.php",
                'AbstractUserRecoveryForm.php',
                "UserRecoveryForm.php",
            ],
            self::CONTROLLER_DIR => [
                //'LoginController.php',
                'UserRegisterController.php',
                'UserRecoveryController.php',
                'UserIndexController.php',
                'UserLogoutController.php',
                'UserNotificationController.php',
                'UserProfileController.php',
                'UserPasswordController.php',
            ]
        ];
    }

    /**
     * @method getAuthArchives
     */
    private function getAuthArchives(): iterable
    {
        yield (new Archive(Archive::LOGIN))
            ->setForm(UserLoginForm::class)
            ->setTemplate($this->useTheme('/pages/user/security/login.html.twig'));

        yield (new Archive('register'))
            ->setRoute('/register')
            ->setController(UserRegisterController::class)
            ->setForm(UserRegisterForm::class)
            ->setTemplate($this->useTheme('/pages/user/security/register.html.twig'));

        yield (new Archive('recovery'))
            ->setRoute('/recovery')
            ->setController(UserRecoveryController::class)
            ->setForm(UserRecoveryForm::class)
            ->setTemplate($this->useTheme('/pages/user/security/recovery.html.twig'));

        yield (new Archive('logout'))
            ->setRoute('/logout')
            ->setTemplate(null)
            ->setController(UserLogoutController::class)
            ->setCustom('endpoint', $this->urlGenerator('/'))
            ->addMenuItem('logout', new TreeNode('logout', [
                'label' => 'logout',
                'href' => $this->urlGenerator('/logout'),
                'icon' => 'bi bi-power',
                'order' => 1024
            ]), $this->userMenu);
    }

    /**
     * @method getPageArchives
     */
    private function getPageArchives(): iterable
    {
        yield (new Archive('index'))
            ->setRoute('/')
            ->setController(UserIndexController::class)
            ->setTemplate($this->useTheme('/pages/user/index.html.twig'))
            ->addMenuItem('index', new TreeNode('dashboard', [
                'label' => 'dashboard',
                'href' => $this->urlGenerator('/'),
                'icon' => 'bi bi-speedometer',
            ]), $this->menu);

        yield (new Archive('notifications'))
            ->setRoute('/notifications')
            ->setController(UserNotificationController::class)
            ->setTemplate($this->useTheme('/pages/user/notifications.html.twig'));

        yield (new Archive('profile'))
            ->setRoute('/profile')
            ->setController(UserProfileController::class)
            ->setTemplate($this->useTheme('/pages/user/profile/main.html.twig'))
            ->addMenuItem('profile', [
                'label' => 'Profile',
                'href' => $this->urlGenerator('/profile'),
                'icon' => 'bi bi-person'
            ], $this->menu)
            ->addMenuItem('profilePill', [
                'label' => 'Profile',
                'href' => $this->urlGenerator('/profile'),
                'icon' => 'bi bi-person-circle',
            ], $this->profileMenu);

        yield (new Archive('password'))
            ->setRoute('/password')
            ->setController(UserPasswordController::class)
            ->setTemplate($this->useTheme('/pages/user/profile/password.html.twig'))
            ->addMenuItem('passwordPill', [
                'label' => 'password',
                'href' => $this->urlGenerator('/password'),
                'icon' => 'bi bi-unlock'
            ], $this->profileMenu);
    }
}
