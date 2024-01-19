<?php

namespace Module\Dashboard\Foundation\Admin\Compact;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Foundation\Admin\Controller\UsersEditorController;
use Module\Dashboard\Foundation\Admin\Controller\UsersInventoryController;
use Module\Dashboard\Foundation\Admin\Form\LoginForm;
use Module\Dashboard\Foundation\System\Compact\Abstract\AbstractDocumentFactory;

final class DocumentFactory extends AbstractDocumentFactory
{
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

    public function createUsersInventoryDocument(): Document
    {
        $document = (new Document())
            ->setName('users:inventory')
            ->setRoute("/users", $this->base)
            ->setTemplate("/users/inventory.html.twig", $this->namespace)
            ->setController(new UsersInventoryController())
        ; 

        $menuContext = [
            'label' => "users",
            'icon' => 'bi bi-people',
            'href' => $document->getUrl(),
            'order' => 1,
        ];

        $document->addMenuItem('main:users', $menuContext, $this->dashboard->menu);

        return $document;
    }

    public function createUserCreatorDocument(): Document
    {
        $inventoryDocument = $this->dashboard->getDocument('users:inventory');

        $document = (new Document())
            ->setName('users:create')
            //->setRoute($inventoryDocument->getRoute())
            ->setTemplate($inventoryDocument->getTemplate())
            ->setController(new UsersEditorController())
        ;

        $menuContext = [
            'label' => "Add new",
            'href' => $document->getUrl(),
            'order' => 0
        ];

        $document->addMenuItem(
            'main:users.create', 
            $menuContext, 
            $inventoryDocument->getMenuItem('main:users')
        );

        return $document;
    }

    // /**
    //  * @method createSettingsPage
    //  */
    // public function createSettingsPage(): PageManager
    // {
    //     $settingsMenuItem = [
    //         'label' => 'settings',
    //         'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_SETTINGS),
    //         'icon' => 'bi bi-wrench',
    //         'order' => 2,
    //     ];

    //     return $this->createPage(AdminDashboardInterface::PAGE_SETTINGS)
    //         ->setController(AdminSettingsController::class)
    //         ->setTemplate($this->dashboard->useTheme('/pages/admin/settings/index.html.twig'))
    //         ->addMenuItem(
    //             AdminDashboardInterface::PAGE_SETTINGS,
    //             $settingsMenuItem,
    //             $this->dashboard->menu
    //         );
    // }

    // /**
    //  * @method createSettingsDefaultPage
    //  */
    // public function createSettingsDefaultPage(): PageManager
    // {
    //     $defaultItem = [
    //         'label' => 'Default',
    //         'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_SETTINGS_DEFAULT),
    //         'icon' => 'bi bi-gear',
    //         'order' => 1,
    //     ];

    //     $defaultForm = new AdminSettingsDefaultForm(
    //         AdminDashboardInterface::PAGE_SETTINGS_DEFAULT,
    //         null,
    //         'POST',
    //         'multipart/form-data'
    //     );

    //     return $this->createPage(AdminDashboardInterface::PAGE_SETTINGS_DEFAULT)
    //         ->setController(AdminSettingsDefaultController::class)
    //         ->setTemplate($this->dashboard->useTheme('/pages/admin/settings/default.html.twig'))
    //         ->addMenuItem(
    //             AdminDashboardInterface::PAGE_SETTINGS_DEFAULT,
    //             $defaultItem,
    //             $this->dashboard->settingsBatch
    //         )
    //         ->setForm($defaultForm);
    // }

    // /**
    //  * @method createSettingsEmailPage
    //  */
    // public function createSettingsEmailPage(): PageManager
    // {
    //     $emailItem = [
    //         'label' => 'Email',
    //         'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_SETTINGS_EMAIL),
    //         'icon' => 'bi bi-envelope-at',
    //         'order' => 2,
    //     ];

    //     $emailForm = new AdminSettingsEmailForm(
    //         AdminDashboardInterface::PAGE_SETTINGS_EMAIL
    //     );

    //     return $this->createPage(AdminDashboardInterface::PAGE_SETTINGS_EMAIL)
    //         ->setController(AdminSettingsEmailController::class)
    //         ->setTemplate($this->dashboard->useTheme('/pages/admin/settings/email.html.twig'))
    //         ->addMenuItem(
    //             AdminDashboardInterface::PAGE_SETTINGS_EMAIL,
    //             $emailItem,
    //             $this->dashboard->settingsBatch
    //         )
    //         ->setForm($emailForm);
    // }

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
