<?php

namespace Module\Dashboard\Foundation\System\Compact\Abstract;

use Module\Dashboard\Bundle\Document\Document;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Foundation\User\Controller\RecoveryController;
use Module\Dashboard\Foundation\User\Controller\LogoutController;
use Module\Dashboard\Foundation\User\Controller\NotificationController;
use Module\Dashboard\Foundation\User\Form\Entity\Security\RecoveryForm;
use Uss\Component\Kernel\UssImmutable;

abstract class AbstractDocumentFactory
{
    protected string $base;

    public function __construct(protected DashboardInterface $dashboard, protected string $namespace)
    {
        $this->base = $this->dashboard->appControl->getBase();
    }

    public function createLoginDocument(): Document
    {
        return (new Document())
            ->setName('login')
            ->setTemplate('/security/login.html.twig', $this->namespace)
            // ->setCustom('app.form', LoginForm())
        ;
    }

    public function createLogoutDocument(): Document
    {
        $document = (new Document())
            ->setName('logout')
            ->setRoute("/logout", $this->base)
            ->setController(new LogoutController())
            ->setCustom('endpoint', $this->dashboard->urlGenerator())
        ;

        $logoutMenuContext = [
            'label' => 'logout',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-power',
            'order' => 1024,
        ];

        $document->addMenuItem('user:logout', $logoutMenuContext, $this->dashboard->userMenu);

        return $document;
    }

    public function createNotificationDocument(): Document
    {
        return (new Document())
             ->setName("notifications")
             ->setRoute("/notifications", $this->base)
             ->setController(new NotificationController())
             ->setTemplate('/pages/notifications.html.twig', $this->dashboard->getTheme(''))
        ;
    }

    public function createPasswordResetDocument(): Document
    {
        return (new Document())
            ->setName('recovery')
            ->setRoute('/reset-password', $this->base)
            ->setTemplate("/security/recovery.html.twig", $this->namespace)
            ->setController(new RecoveryController())
            ->setCustom('app.form', new RecoveryForm())
        ;
    }

    public function createIndexDocument(): Document
    {
        $document = (new Document())
            ->setName("index")
            ->setRoute('/', $this->base)
            ->setTemplate("/index.html.twig", $this->namespace)
            ->setContext([
                'app' => [
                    'title' => UssImmutable::PROJECT_NAME,
                    'website' => UssImmutable::PROJECT_WEBSITE,
                    'documentation' => UssImmutable::PROJECT_WEBSITE . '/documentation',
                    'version' => '5',
                    'authorEmail' => UssImmutable::AUTHOR_EMAIL,
                    'githubRepository' => DashboardImmutable::GITHUB_REPO,
                ],
            ])
        ;

        $indexMenuContext = [
            'label' => 'dashboard',
            'href' => $document->getUrl(),
            'order' => 1,
        ];

        $document->addMenuItem('main:index', $indexMenuContext, $this->dashboard->menu);

        return $document;
    }
}