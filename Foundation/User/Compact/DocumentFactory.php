<?php

namespace Module\Dashboard\Foundation\User\Compact;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\DashboardInterface;
use Module\Dashboard\Foundation\User\Controller\IndexController;
use Module\Dashboard\Foundation\User\Controller\LogoutController;
use Module\Dashboard\Foundation\User\Controller\NotificationController;
use Module\Dashboard\Foundation\User\Controller\RecoveryController;
use Module\Dashboard\Foundation\User\Controller\RegisterController;
use Module\Dashboard\Foundation\User\Form\LoginForm;
use Module\Dashboard\Foundation\User\Form\RecoveryForm;
use Module\Dashboard\Foundation\User\Form\RegisterForm;

final class DocumentFactory
{
    protected string $base;

    public function __construct(
        protected DashboardInterface $dashboard,
        protected string $namespace
    ){
        $this->base = $this->dashboard->appControl->getBase();
    }

    public function createLoginDocument(): Document
    {
        return (new Document())
            ->setName('login')
            ->setTemplate('/security/login.html.twig', $this->namespace)
            ->setCustom('login:form', new LoginForm('login'))
            ;
    }

    public function createRegisterDocument(): Document
    {
        return (new Document())
            ->setName('register')
            ->setRoute('/register', $this->base)
            ->setTemplate('/security/register.html.twig', $this->namespace)
            ->setCustom('register:form', new RegisterForm('register'))
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
            ->setCustom('recovery:form', new RecoveryForm('recovery'))
            ;
    }

    public function createLogoutDocument(): Document
    {
        $document = (new Document())
            ->setName('logout')
            ->setRoute("/logout", $this->base)
            ->setController(new LogoutController())
            ->setCustom('endpoint', $this->dashboard->urlGenerator());
        
        $document->addMenuItem('logout', [
            'label' => 'logout',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-power',
            'order' => 1024,
        ], $this->dashboard->userMenu);
        
        return $document;
    }

    public function createIndexDocument(): Document
    {
        $document = (new Document())
            ->setName("index")
            ->setRoute('/', $this->base)
            ->setController(new IndexController())
            ->setTemplate("/index.html.twig", $this->namespace)
            ;

        $document->addMenuItem('index', [
            'label' => 'dashboard',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-speedometer',
            'order' => 0,
        ], $this->dashboard->menu);

        return $document;
    }

    public function createNotificationDocument(): Document
    {
       return (new Document())
            ->setName("notification")
            ->setController(new NotificationController())
            ->setTemplate('')
            ;
    }

    // /**
    //  * @method createUserProfilePage
    //  */
    // public function createUserProfilePage(): PageManager
    // {
    //     $profileNavigation = [
    //         'label' => 'Profile',
    //         'href' => $this->dashboard->urlGenerator('/' . UserDashboardInterface::PAGE_USER_PROFILE),
    //         'icon' => 'bi bi-person'
    //     ];
        
    //     $profilePillNavigation = [
    //         'label' => 'Profile',
    //         'href' => $this->dashboard->urlGenerator('/' . UserDashboardInterface::PAGE_USER_PROFILE),
    //         'icon' => 'bi bi-person-circle',
    //     ];
        
    //     return $this->createPage(UserDashboardInterface::PAGE_USER_PROFILE)
    //         ->setController(UserProfileController::class)
    //         ->setTemplate($this->dashboard->useTheme('/pages/user/profile/main.html.twig'))
    //         ->addMenuItem(
    //             UserDashboardInterface::PAGE_USER_PROFILE, 
    //             $profileNavigation, 
    //             $this->dashboard->menu
    //         )
    //         ->addMenuItem(
    //             'profile-batch-profile', 
    //             $profilePillNavigation, 
    //             $this->dashboard->profileBatch
    //         );
    // }

    // /**
    //  * @method createUserPasswordPage
    //  */
    // public function createUserPasswordPage(): PageManager
    // {
    //     $passwordPillNavigation = [
    //         'label' => 'password',
    //         'href' => $this->dashboard->urlGenerator('/' . UserDashboardInterface::PAGE_USER_PASSWORD),
    //         'icon' => 'bi bi-unlock'
    //     ];

    //     return $this->createPage(UserDashboardInterface::PAGE_USER_PASSWORD)
    //         ->setController(UserPasswordController::class)
    //         ->setTemplate($this->dashboard->useTheme('/pages/user/profile/password.html.twig'))
    //         ->addMenuItem(
    //             'profile-batch-password', 
    //             $passwordPillNavigation, 
    //             $this->dashboard->profileBatch
    //         );
    // }
}