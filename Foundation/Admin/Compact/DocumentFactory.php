<?php

namespace Module\Dashboard\Foundation\Admin\Compact;

use Module\Dashboard\Bundle\Document\Document;
use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Foundation\Admin\Controller\SystemInfoController;
use Module\Dashboard\Foundation\Admin\Controller\Settings\EmailSettingsController;
use Module\Dashboard\Foundation\Admin\Controller\Settings\SystemSettingsController;
use Module\Dashboard\Foundation\Admin\Controller\Settings\UsersSettingsController;
use Module\Dashboard\Foundation\Admin\Controller\SettingsController;
use Module\Dashboard\Foundation\Admin\Controller\UsersController;
use Module\Dashboard\Foundation\Admin\Form\LoginForm;
use Module\Dashboard\Foundation\Admin\Form\Settings\EmailSettingsForm;
use Module\Dashboard\Foundation\Admin\Form\Settings\SystemSettingsForm;
use Module\Dashboard\Foundation\System\Compact\Abstract\AbstractDocumentFactory;
use Ucscode\TreeNode\TreeNode;
use Uss\Component\Kernel\Uss;

final class DocumentFactory extends AbstractDocumentFactory
{
    protected Document $settingsDocument;
    
    public function createLoginDocument(): Document
    {
        return parent::createLoginDocument()
            ->setCustom('app.form', new LoginForm());
    }
    
    public function createIndexDocument(): Document
    {
        $document = parent::createIndexDocument();
        $context = $document->getContext();
        $document->setContext($context + [
            'btc_wallet' => 'bc1qerasn0dtsf2h0sssyz5auqnj6qzugrmw0n4wm9'
        ]);
        $document->getMenuItem('main:index')
            ->setAttribute('icon', 'bi bi-microsoft')
            ->setAttribute('label', 'CPanel');
        return $document;
    }

    public function createPasswordResetDocument(): Document
    {
        $document = parent::createPasswordResetDocument();
        $document->getCustom('app.form')
            ->setProperty('dashboardInterface', $this->dashboard);
        return $document;
    }

    public function createUsersDocument(): Document
    {
        $document = (new Document())
            ->setRoute("/users", $this->base)
            ->setTemplate("/users/base.html.twig", $this->namespace)
            ->setController(new UsersController())
        ; 

        $inventoryMenuContext = new TreeNode('main:users', [
            'label' => "users",
            'icon' => 'bi bi-people',
            'href' => $document->getUrl(),
            'order' => 2,
            'auto-focus' => false,
        ]);

        $creatorMenuContext = [
            'label' => "Add new",
            'href' => Uss::instance()->replaceUrlQuery([
                'channel' => CrudEnum::CREATE->value,
            ], $document->getUrl()),
            'order' => 1,
            'auto-focus' => false,
        ];

        $document->addMenuItem('main:users', $inventoryMenuContext, $this->dashboard->menu);
        $document->addMenuItem('main:users.create', $creatorMenuContext, $inventoryMenuContext);

        return $document;
    }

    public function createSettingsDocument(): Document
    {
        $document = (new Document())
            ->setController(new SettingsController())
            ->setTemplate('/settings/index.html.twig', $this->namespace)
            ->setRoute('/settings', $this->base)
        ;

        $settingsMenuContext = [
            'label' => 'settings',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-wrench',
            'order' => 3,
        ];

        $document->addMenuItem('main:settings', $settingsMenuContext, $this->dashboard->menu);

        $this->settingsDocument = $document;

        return $document;
    }

    public function createSystemSettingsDocument(): Document
    {
        $document = (new Document())
            ->setController(new SystemSettingsController())
            ->setTemplate('/settings/system.html.twig', $this->namespace)
            ->setRoute('/settings/system', $this->base)
            ->setCustom('app.form', new SystemSettingsForm())
        ;

        $systemSettingsMenuContext = [
            'label' => 'System',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-gear',
            'order' => 1,
        ];

        $document->addMenuItem('settings:system', $systemSettingsMenuContext, $this->dashboard->settingsBatch);

        return $document;
    }

    public function createEmailSettingsDocument(): Document
    {
        $document = (new Document())
            ->setController(new EmailSettingsController())
            ->setTemplate('/settings/email.html.twig', $this->namespace)
            ->setRoute('/settings/email', $this->base)
            ->setCustom('app.form', new EmailSettingsForm())
        ;
        
        $emailSettingsMenuContext = [
            'label' => 'Email',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-envelope-at',
            'order' => 2,
        ];

        $document->addMenuItem('settings:email', $emailSettingsMenuContext, $this->dashboard->settingsBatch);

        return $document;
    }

    public function createUsersSettingsDocument(): Document
    {
        $document = (new Document())
            ->setController(new UsersSettingsController())
            ->setTemplate('/settings/user.html.twig', $this->namespace)
            ->setRoute('/settings/users', $this->base)
            ->setCustom('app.form', null)
        ;

        $usersSettingsMenuContext = [
            'label' => 'Users',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-people',
            'order' => 3,
        ];

        $document->addMenuItem('settings:users', $usersSettingsMenuContext, $this->dashboard->settingsBatch);

        return $document;
    }

    public function createSystemInfoDocument(): Document
    {
        $document = (new Document())
            ->setController(new SystemInfoController())
            ->setTemplate('/system-info.html.twig', $this->namespace)
            ->setRoute('/system-info', $this->base)
        ;

        $infoMenuContext = [
            "label" => "System Info",
            "href" => $document->getUrl(),
            "order" => 1,
        ];
        
        $document->addMenuItem(
            'main:settings.systemInfo', 
            $infoMenuContext,
            $this->settingsDocument->getMenuItem('main:settings')
        );

        return $document;
    }
}
