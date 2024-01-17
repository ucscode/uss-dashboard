<?php

namespace Module\Dashboard\Foundation\Admin\Compact;

use Module\Dashboard\Bundle\Common\Document;
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
        $document = (new Document())
            ->setName('index')
            ->setRoute("/", $this->base)
            ->setTemplate("/index.html.twig", $this->namespace)
            ->setContext([])
        ;

        $indexMenuContext = [
            'label' => 'Cpanel',
            'href' => $document->getUrl(),
            'icon' => 'bi bi-microsoft',
            'order' => 0,
        ];

        $document->addMenuItem('main:index', $indexMenuContext, $this->dashboard->menu);

        return $document;
    }

    public function createPasswordResetDocument(): Document
    {
        $document = parent::createPasswordResetDocument();
        $document->getCustom('app.form')
            ->setProperty('dashboardInterface', $this->dashboard);
        return $document;
    }

    // /**
    //  * @method createNotificationPage
    //  */
    // public function createNotificationPage(): PageManager
    // {
    //     return $this->createPage(AdminDashboardInterface::PAGE_NOTIFICATIONS)
    //         ->setController(UserNotificationController::class)
    //         ->setTemplate($this->dashboard->useTheme('/pages/notifications.html.twig'));
    // }

    // /**
    //  * @method createUsersPage
    //  */
    // public function createUsersPage(): PageManager
    // {
    //     $userMenuItem = [
    //         'label' => 'Users',
    //         'icon' => 'bi bi-people-fill',
    //         'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_USERS),
    //         'order' => 1,
    //     ];

    //     return $this->createPage(AdminDashboardInterface::PAGE_USERS)
    //         ->setController(AdminUserController::class)
    //         ->setTemplate($this->dashboard->useTheme('/pages/admin/users.html.twig'))
    //         ->addMenuItem(
    //             AdminDashboardInterface::PAGE_USERS,
    //             $userMenuItem,
    //             $this->dashboard->menu
    //         );
    // }

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
