<?php

class AdminDashboard extends AbstractDashboard
{
    use SingletonTrait;

    public const DIR = DashboardImmutable::SRC_DIR . '/Admin';

    public const FORM_DIR = self::DIR . '/forms';
    public const TEMPLATE_DIR = self::DIR . '/templates';
    public const CONTROLLER_DIR = self::DIR . '/controllers';

    protected function createProject(): void
    {
        $uss = Uss::instance();
        $uss->addTwigFilesystem(self::TEMPLATE_DIR, 'Ua');
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
                ->set('form', UserLoginForm::class)
                ->set('template', '@Ua/security/login.html.twig'),

            ],

            'pages' => [

                (new Archive('index'))
                ->set('template', '@Ua/index.html.twig')
                ->set('controller', AdminIndexController::class)
                ->set('route', '/'),

            ],

        ];

        foreach($archiveList as $section => $archives) {
            foreach($archives as $archive) {
                $this->archiveRepository->addArchive($archive->name, $archive);
            }
        }
    }
}
