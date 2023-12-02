<?php

class AdminPageFactory extends AbstractPageFactory
{
    /**
     * @method createLoginPage
     */
    public function createLoginPage(): PageManager
    {
        $loginForm = new AdminLoginForm(PageManager::LOGIN);

        return $this->createPage(PageManager::LOGIN, false)
            ->setForm($loginForm)
            ->setTemplate($this->dashboard->useTheme('/pages/admin/security/login.html.twig'));
    }

    /**
     * @method createLogoutPage
     */
    public function createLogoutPage(): PageManager
    {
        $logoutMenuItem = [
            'icon' => 'bi bi-power',
            'order' => 1024,
            'label' => 'logout',
            'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_LOGOUT)
        ];

        return $this->createPage(AdminDashboardInterface::PAGE_LOGOUT)
            ->setController(UserLogoutController::class)
            ->addMenuItem(
                AdminDashboardInterface::PAGE_LOGOUT,
                $logoutMenuItem,
                $this->dashboard->userMenu
            )
            ->setCustom('endpoint', $this->dashboard->urlGenerator());
    }

    /**
     * @method createIndexPage
     */
    public function createIndexPage(): PageManager
    {
        $indexMenuItem = [
            'label' => 'Dashboard',
            'icon' => 'bi bi-microsoft',
            'href' => $this->dashboard->urlGenerator()
        ];

        return $this->createPage(AdminDashboardInterface::PAGE_INDEX)
            ->setRoute('/')
            ->setController(AdminIndexController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/admin/index.html.twig'))
            ->addMenuItem(
                AdminDashboardInterface::PAGE_INDEX,
                $indexMenuItem,
                $this->dashboard->menu
            );
    }

    /**
     * @method createNotificationPage
     */
    public function createNotificationPage(): PageManager
    {
        return $this->createPage(AdminDashboardInterface::PAGE_NOTIFICATIONS)
            ->setController(UserNotificationController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/notifications.html.twig'));
    }

    /**
     * @method createUsersPage
     */
    public function createUsersPage(): PageManager
    {
        $userMenuItem = [
            'label' => 'Users',
            'icon' => 'bi bi-people-fill',
            'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_USERS),
        ];

        return $this->createPage(AdminDashboardInterface::PAGE_USERS)
            ->setController(AdminUserController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/admin/users.html.twig'))
            ->addMenuItem(
                AdminDashboardInterface::PAGE_USERS,
                $userMenuItem,
                $this->dashboard->menu
            );
    }

    /**
     * @method createSettingsPage
     */
    public function createSettingsPage(): PageManager
    {
        $settingsMenuItem = [
            'label' => 'settings',
            'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_SETTINGS),
            'icon' => 'bi bi-wrench'
        ];

        return $this->createPage(AdminDashboardInterface::PAGE_SETTINGS)
            ->setController(AdminSettingsController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/admin/settings/index.html.twig'))
            ->addMenuItem(
                AdminDashboardInterface::PAGE_SETTINGS,
                $settingsMenuItem,
                $this->dashboard->menu
            );
    }

    /**
     * @method createSettingsDefaultPage
     */
    public function createSettingsDefaultPage(): PageManager
    {
        $defaultItem = [
            'label' => 'Default',
            'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_SETTINGS_DEFAULT),
            'icon' => 'bi bi-gear',
            'order' => 1,
        ];

        $defaultForm = new AdminSettingsDefaultForm(
            AdminDashboardInterface::PAGE_SETTINGS_DEFAULT,
            null,
            'POST',
            'multipart/form-data'
        );

        return $this->createPage(AdminDashboardInterface::PAGE_SETTINGS_DEFAULT)
            ->setController(AdminSettingsDefaultController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/admin/settings/default.html.twig'))
            ->addMenuItem(
                AdminDashboardInterface::PAGE_SETTINGS_DEFAULT,
                $defaultItem,
                $this->dashboard->settingsBatch
            )
            ->setForm($defaultForm);
    }

    /**
     * @method createSettingsEmailPage
     */
    public function createSettingsEmailPage(): PageManager
    {
        $emailItem = [
            'label' => 'Email',
            'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_SETTINGS_EMAIL),
            'icon' => 'bi bi-envelope-at',
            'order' => 2,
        ];

        $emailForm = new AdminSettingsEmailForm(
            AdminDashboardInterface::PAGE_SETTINGS_EMAIL
        );

        return $this->createPage(AdminDashboardInterface::PAGE_SETTINGS_EMAIL)
            ->setController(AdminSettingsEmailController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/admin/settings/email.html.twig'))
            ->addMenuItem(
                AdminDashboardInterface::PAGE_SETTINGS_EMAIL,
                $emailItem,
                $this->dashboard->settingsBatch
            )
            ->setForm($emailForm);
    }

    /**
     * @method createSettingsUserPage
     */
    public function createSettingsUserPage(): PageManager
    {
        $userItem = [
            'label' => 'Users',
            'href' => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_SETTINGS_USERS),
            'icon' => 'bi bi-people',
            'order' => 3,
        ];

        $userForm = new AdminSettingsUserForm(
            AdminDashboardInterface::PAGE_SETTINGS_USERS
        );

        return $this->createPage(AdminDashboardInterface::PAGE_SETTINGS_USERS)
            ->setController(AdminSettingsUserController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/admin/settings/user.html.twig'))
            ->addMenuItem(
                AdminDashboardInterface::PAGE_SETTINGS_USERS,
                $userItem,
                $this->dashboard->settingsBatch
            )
            ->setForm($userForm);
    }

    /**
     * @method createInfoPage
     */
    public function createInfoPage(): PageManager
    {
        $infoItem = [
            "label" => "info",
            "href" => $this->dashboard->urlGenerator('/' . AdminDashboardInterface::PAGE_INFO)
        ];

        $parentItem = $this->dashboard->pageRepository->getPageManager(
            AdminDashboardInterface::PAGE_SETTINGS
        )->getMenuItem(AdminDashboardInterface::PAGE_SETTINGS, true);

        return $this->createPage(AdminDashboardInterface::PAGE_INFO)
            ->setController(AdminInfoController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/admin/info.html.twig'))
            ->addMenuItem(
                AdminDashboardInterface::PAGE_INFO,
                $infoItem,
                $parentItem
            );
    }
}
