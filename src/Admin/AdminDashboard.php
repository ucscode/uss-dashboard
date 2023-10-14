<?php

class AdminDashboard extends AbstractDashboard
{
    use SingletonTrait;

    public const DIR = DashboardImmutable::SRC_DIR . '/Admin';
    public const TEMPLATE_DIR = self::DIR . '/templates';
    public const CONTROLLER_DIR = self::DIR . '/controllers';
    public const FORM_DIR = self::DIR . '/forms';

    protected function createProject(): void
    {
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
        $archives = [

            (new Archive(Archive::LOGIN))
                ->set('form', UserLoginForm::class)
                ->set('template', '@Ua/security/login.html.twig'),

            (new Archive('index'))
                ->set('template', '@Ua/index.html.twig')
                ->set('controller', AdminIndexController::class)
                ->set('route', '/'),

        ];
        
        $ar = new ArchiveRepository($this::class);

        foreach($archives as $archive) {
            $ar->addArchive($archive->name, $archive);
        }
    }
}
