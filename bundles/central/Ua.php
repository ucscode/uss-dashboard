<?php

class Ua extends AbstractUd
{
    use SingletonTrait;

    public const DIR = self::SRC_DIR . '/Admin';
    public const TEMPLATE_DIR = self::DIR . '/templates';
    public const CONTROLLER_DIR = self::DIR . '/controllers';

    public function createProject(array $config): void
    {
        parent::createProject($config);
        $this->includeControllers();
        $this->registerArchives();
        $this->emitEvents();
    }

    protected function includeControllers() {

        $source = [
            self::CONTROLLER_DIR => [
                'IndexController.php',
            ]
        ];

        foreach($source as $path => $files) {
            foreach($files as $file) {
                $controller = $path . '/' . $file;
                require_once $controller;
            }
        }

    }

    protected function registerArchives(): void {
        
        $archives = [

            (new Archive(Archive::LOGIN))
                ->set('form', UdLoginForm::class)
                ->set('template', '@Ua/security/login.html.twig'),

            (new Archive('index'))
                ->set('template', '@Ua/index.html.twig')
                ->set('controller', IndexController::class)
                ->set('route', '/'),
            
        ];

        foreach($archives as $archive) {
            $this->addArchive($archive);
        }

    }
}
