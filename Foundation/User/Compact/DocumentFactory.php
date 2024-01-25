<?php

namespace Module\Dashboard\Foundation\User\Compact;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Foundation\System\Compact\Abstract\AbstractDocumentFactory;
use Module\Dashboard\Foundation\User\Controller\PasswordController;
use Module\Dashboard\Foundation\User\Controller\ProfileController;
use Module\Dashboard\Foundation\User\Controller\RegisterController;
use Module\Dashboard\Foundation\User\Form\Entity\Security\LoginForm;
use Module\Dashboard\Foundation\User\Form\Entity\System\ProfileForm;
use Module\Dashboard\Foundation\User\Form\Entity\Security\RegisterForm;
use Module\Dashboard\Foundation\User\Form\Entity\System\PasswordForm;

final class DocumentFactory extends AbstractDocumentFactory
{
    public function createLoginDocument(): Document
    {
        return parent::createLoginDocument()
            ->setCustom("app.form", new LoginForm());
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

    public function createIndexDocument(): Document
    {
        $document = parent::createIndexDocument();
        $document->getMenuItem('main:index')->setAttribute('icon', 'bi bi-speedometer');
        return $document;
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
                $profileMenuContext + ['order' => 2], 
                $this->dashboard->menu
            )
            ->addMenuItem(
                'profile:primary', 
                $profileMenuContext + ['order' => 1], 
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
            'order' => 2,
            // 'auto-focus' => false,
        ];

        $document->addMenuItem('profile:password', $passwordMenuContext, $this->dashboard->profileBatch);

        return $document;
    }
}
