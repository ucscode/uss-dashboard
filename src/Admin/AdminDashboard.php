<?php

class AdminDashboard extends AbstractDashboard
{
    use SingletonTrait;

    public const DIR = DashboardImmutable::SRC_DIR . '/Admin';

    public const FORM_DIR = self::DIR . '/forms';
    public const TEMPLATE_DIR = self::DIR . '/templates';
    public const CONTROLLER_DIR = self::DIR . '/controllers';
    public const ASSETS_DIR = self::DIR . '/assets';

    protected function main(): void
    {
        $uss = Uss::instance();
        $this->includeControllers();
        $this->registerArchives();
    }

    protected function includeControllers()
    {
        $source = [
            self::CONTROLLER_DIR => [
                'AdminIndexController.php',
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
        $archiveList = [
            'security' => [
                (new Archive(Archive::LOGIN))
                    ->setForm(UserLoginForm::class)
                    ->setTemplate('@Ua/security/login.html.twig'),
            ],

            'pages' => [
                (new Archive('index'))
                    ->setTemplate('@Ua/index.html.twig')
                    ->setController(AdminIndexController::class)
                    ->setRoute('/'),
            ],
        ];

        foreach($archiveList as $section => $archives) {
            foreach($archives as $archive) {
                $this->archiveRepository->addArchive($archive->name, $archive);
            }
        }
    }
}
