<?php

namespace Module\Dashboard\Foundation\Admin\Compact;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Foundation\Admin\Controller\Settings\EmailSettingsController;
use Module\Dashboard\Foundation\Admin\Controller\Settings\SystemSettingsController;
use Module\Dashboard\Foundation\Admin\Controller\SettingsController;
use Module\Dashboard\Foundation\Admin\Controller\UsersController;
use Module\Dashboard\Foundation\Admin\Form\LoginForm;
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
            ->setName('users:inventory')
            ->setRoute("/users", $this->base)
            ->setTemplate("/users/base.html.twig", $this->namespace)
            ->setController(new UsersController())
        ; 

        $inventoryMenuContext = new TreeNode('main:users', [
            'label' => "users",
            'icon' => 'bi bi-people',
            'href' => $document->getUrl(),
            'order' => 1,
            'auto-focus' => false,
        ]);

        $creatorMenuContext = [
            'label' => "Add new",
            'href' => Uss::instance()->replaceUrlQuery([
                'channel' => CrudEnum::CREATE->value,
            ], $document->getUrl()),
            'order' => 0,
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
            'order' => 2,
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
            ->setCustom('app.form', null)
        ;

        $systemSettingsMenuContext = [
            'label' => 'System',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-gear',
            'order' => 0,
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
            ->setCustom('app.form', null)
        ;
        
        $emailItem = [
            'label' => 'Email',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-envelope-at',
            'order' => 2,
        ];

        return $document;
    }

    // /**
    //  * @method createSettingsUserPage
    //  */
    // public function createSettingsUserPage(): PageManager
    // {
    //     $userItem = [
    //         'label' => 'Users',
    //         'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_SETTINGS_USERS),
    //         'icon' => 'bi bi-people',
    //         'order' => 3,
    //     ];

    //     $userForm = new AdminSettingsUserForm(
    //         AdminDashboardInterface::PAGE_SETTINGS_USERS
    //     );

    //     return $this->createPage(AdminDashboardInterface::PAGE_SETTINGS_USERS)
    //         ->setController(AdminSettingsUserController::class)
    //         ->setTemplate($this->dashboard->useTheme('/pages/admin/settings/user.html.twig'))
    //         ->addMenuItem(
    //             AdminDashboardInterface::PAGE_SETTINGS_USERS,
    //             $userItem,
    //             $this->dashboard->settingsBatch
    //         )
    //         ->setForm($userForm);
    // }

    // /**
    //  * @method createInfoPage
    //  */
    // public function createInfoPage(): PageManager
    // {
    //     $infoItem = [
    //         "label" => "information",
    //         "href" => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_INFO)
    //     ];

    //     $parentItem = $this->dashboard->pageRepository->getPageManager(
    //         AdminDashboardInterface::PAGE_SETTINGS
    //     )->getMenuItem(AdminDashboardInterface::PAGE_SETTINGS, true);

    //     return $this->createPage(AdminDashboardInterface::PAGE_INFO)
    //         ->setController(AdminInfoController::class)
    //         ->setTemplate($this->dashboard->useTheme('/pages/admin/info.html.twig'))
    //         ->addMenuItem(
    //             AdminDashboardInterface::PAGE_INFO,
    //             $infoItem,
    //             $parentItem
    //         );
    // }
}
