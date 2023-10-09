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
        $this->registerArchives();
        parent::emitEvents();
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
