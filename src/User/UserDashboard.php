<?php

use Ucscode\Packages\TreeNode;

class UserDashboard extends AbstractDashboard
{
    use SingletonTrait;

    public const DIR = DashboardImmutable::SRC_DIR . '/User';
    
    public const FORMS_DIR = self::DIR . '/forms';
    public const TEMPLATE_DIR = self::DIR . "/templates";
    public const CONTROLLER_DIR = self::DIR . '/controllers';
    public const ASSETS_DIR = self::DIR . '/assets';

    public readonly TreeNode $profileMenu;

    protected function createProject(): void
    {
        $uss = Uss::instance();
        $this->profileMenu = new TreeNode('profileMenu');
        $this->includeControllers();
        $this->registerArchives();
        $this->preload();
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
                'UserProfileController.php',
                'UserPasswordController.php',
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
        $archiveList = [
            'security' => [
                (new Archive(Archive::LOGIN))
                    ->setForm(UserLoginForm::class)
                    ->setTemplate('@Ud/security/login.html.twig'),
        
                (new Archive('register'))
                    ->setRoute('/register')
                    ->setTemplate('@Ud/security/register.html.twig')
                    ->setController(UserRegisterController::class)
                    ->setForm(UserRegisterForm::class),
        
                (new Archive('recovery'))
                    ->setRoute('/recovery')
                    ->setTemplate('@Ud/security/register.html.twig')
                    ->setController(UserRecoveryController::class)
                    ->setForm(UserRecoveryForm::class),
        
                (new Archive('logout'))
                    ->setRoute('/logout')
                    ->setTemplate(null)
                    ->setController(UserLogoutController::class)
                    ->setCustom('endpoint', $this->urlGenerator('/'))
                    ->addMenuItem('logout', new TreeNode('logout', [
                        'label' => 'logout',
                        'href' => $this->urlGenerator('/logout'),
                        'icon' => 'bi bi-power',
                        'order' => 1024
                    ]), $this->userMenu),
            ],
        
            'pages' => [
                (new Archive('index'))
                    ->setRoute('/')
                    ->setTemplate('@Ud/pages/welcome.html.twig')
                    ->setController(UserIndexController::class)
                    ->addMenuItem('index', new TreeNode('dashboard', [
                        'label' => 'dashboard',
                        'href' => $this->urlGenerator('/'),
                        'icon' => 'bi bi-speedometer',
                    ]), $this->menu),
        
                (new Archive('notifications'))
                    ->setRoute('/notifications')
                    ->setTemplate('@Ud/pages/notifications.html.twig')
                    ->setController(UserNotificationController::class),
        
                (new Archive('profile'))
                    ->setRoute('/profile')
                    ->setTemplate('@Ud/pages/profile/main.html.twig')
                    ->setController(UserProfileController::class)
                    ->addMenuItem('profile', [
                        'label' => 'Profile',
                        'href' => $this->urlGenerator('/profile'),
                        'icon' => 'bi bi-person'
                    ], $this->menu)
                    ->addMenuItem('profilePill', [
                        'label' => 'Profile',
                        'href' => $this->urlGenerator('/profile'),
                        'icon' => 'bi bi-person-circle',
                    ], $this->profileMenu),
        
                (new Archive('password'))
                    ->setRoute('/password')
                    ->setTemplate('@Ud/pages/profile/password.html.twig')
                    ->setController(UserPasswordController::class)
                    ->addMenuItem('passwordPill', [
                        'label' => 'password',
                        'href' => $this->urlGenerator('/password'),
                        'icon' => 'bi bi-unlock'
                    ], $this->profileMenu),
            ],
        ];
        
        foreach($archiveList as $section => $archives) {
            foreach($archives as $archive) {
                $this->archiveRepository->addArchive($archive->name, $archive);
            }
        };

    }

    protected function preload()
    {
        Event::instance()->addListener('dashboard:render', function () {
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

}
