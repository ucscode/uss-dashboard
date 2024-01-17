<?php

namespace Module\Dashboard\Foundation\User\Compact;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Foundation\System\Compact\Abstract\AbstractDocumentFactory;
use Module\Dashboard\Foundation\User\Controller\LogoutController;
use Module\Dashboard\Foundation\User\Controller\NotificationController;
use Module\Dashboard\Foundation\User\Controller\PasswordController;
use Module\Dashboard\Foundation\User\Controller\ProfileController;
use Module\Dashboard\Foundation\User\Controller\RecoveryController;
use Module\Dashboard\Foundation\User\Controller\RegisterController;
use Module\Dashboard\Foundation\User\Form\Entity\Security\LoginForm;
use Module\Dashboard\Foundation\User\Form\Entity\System\ProfileForm;
use Module\Dashboard\Foundation\User\Form\Entity\Security\RecoveryForm;
use Module\Dashboard\Foundation\User\Form\Entity\Security\RegisterForm;
use Module\Dashboard\Foundation\User\Form\Entity\System\PasswordForm;
use Uss\Component\Kernel\UssImmutable;

final class DocumentFactory extends AbstractDocumentFactory
{
    public function createLoginDocument(): Document
    {
        return (new Document())
            ->setName('login')
            ->setTemplate('/security/login.html.twig', $this->namespace)
            ->setCustom('app.form', new LoginForm())
        ;
    }

    public function createRegisterDocument(): Document
    {
        return (new Document())
            ->setName('register')
            ->setRoute('/register', $this->base)
            ->setTemplate('/security/register.html.twig', $this->namespace)
            ->setCustom('app.form', new RegisterForm())
            ->setController(new RegisterController())
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

    public function createIndexDocument(): Document
    {
        $document = (new Document())
            ->setName("index")
            ->setRoute('/', $this->base)
            ->setTemplate("/index.html.twig", $this->namespace)
            ->setContext([
                'title' => UssImmutable::PROJECT_NAME,
                'app_webpage' => UssImmutable::PROJECT_WEBSITE,
                'app_version' => '5.5',
                'author_email' => UssImmutable::AUTHOR_EMAIL,
                'github_repo' => DashboardImmutable::GITHUB_REPO,
            ])
        ;

        $indexMenuContext = [
            'label' => 'dashboard',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-speedometer',
            'order' => 1,
        ];

        $document->addMenuItem('main:index', $indexMenuContext, $this->dashboard->menu);

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

    /**
     * @method createUserProfileDocument
     */
    public function createUserProfileDocument(): Document
    {
        $document = (new Document())
            ->setName('profile')
            ->setRoute('/profile', $this->base)
            ->setController(new ProfileController())
            ->setTemplate("/profile/main.html.twig", $this->namespace)
            ->setCustom('app.form', new ProfileForm())
        ;

        $profileMenuContext = [
            'label' => 'Profile',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-person',
        ];

        $document
            ->addMenuItem(
                'main:profile', 
                $profileMenuContext + ['order' => 1], 
                $this->dashboard->menu
            )
            ->addMenuItem(
                'profile:primary', 
                $profileMenuContext + ['order' => 0], 
                $this->dashboard->profileBatch
            );

        return $document;
    }

    /**
     * @method createUserPasswordPage
     */
    public function createUserProfilePasswordDocument(): Document
    {
        $document = (new Document())
            ->setController(new PasswordController())
            ->setRoute("/profile/password", $this->base)
            ->setTemplate("/profile/password.html.twig", $this->namespace)
            ->setCustom("app.form", new PasswordForm())
        ;

        $passwordMenuContext = [
            'label' => 'password',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-unlock',
            'order' => 1,
            // 'autoFocus' => false,
        ];

        $document->addMenuItem('profile:password', $passwordMenuContext, $this->dashboard->profileBatch);

        return $document;
    }
}
