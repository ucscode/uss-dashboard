<?php

namespace Module\Dashboard\Foundation\System\Compact\Abstract;

use Module\Dashboard\Bundle\Document\Document;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use Module\Dashboard\Foundation\User\Controller\RecoveryController;
use Module\Dashboard\Foundation\User\Controller\LogoutController;
use Module\Dashboard\Foundation\User\Controller\NotificationController;
use Module\Dashboard\Foundation\User\Form\Entity\Security\RecoveryForm;
use Uss\Component\Kernel\UssImmutable;

abstract class AbstractDocumentFactory
{
    protected string $base;
    protected string $themeNamespaceAuth;
    protected string $themeNamespaceError;
    protected string $themeNamespaceSystem;
    protected string $foundationNamespaceSystem = '@Foundation/System/Template';

    public function __construct(protected AbstractDashboard $dashboard, protected string $namespace)
    {
        $this->base = $this->dashboard->appControl->getUrlBasePath();
        $this->themeNamespaceAuth = $this->dashboard->getTheme('/pages/auth');
        $this->themeNamespaceError = $this->dashboard->getTheme('/pages/error');
        $this->themeNamespaceSystem = $this->dashboard->getTheme('/pages/system');
    }

    public function createLoginDocument(): Document
    {
        return (new Document())
            ->setName('login')
            ->setTemplate('/login.html.twig', $this->themeNamespaceAuth)
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
             ->setTemplate('/notifications.html.twig', $this->foundationNamespaceSystem)
        ;
    }

    public function createPasswordResetDocument(): Document
    {
        return (new Document())
            ->setName('recovery')
            ->setRoute('/reset-password', $this->base)
            ->setTemplate("/reset-password.html.twig", $this->themeNamespaceAuth)
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