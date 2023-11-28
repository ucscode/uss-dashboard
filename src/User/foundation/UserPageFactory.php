<?php

use Ucscode\TreeNode\TreeNode;

final class UserPageFactory extends AbstractPageFactory
{
    /**
     * @method createLoginPage
     */
    public function createLoginPage(): PageManager
    {
        $loginForm = new UserLoginForm(PageManager::LOGIN);

        return $this->createPage(PageManager::LOGIN, false)
            ->setForm($loginForm)
            ->setTemplate($this->dashboard->useTheme('/pages/user/security/login.html.twig'));
    }
    
    /**
     * @method createRegisterPage
     */
    public function createRegisterPage(): PageManager
    {
        $registerationForm = new UserRegisterForm(UserDashboardInterface::PAGE_REGISTER);

        return $this->createPage(UserDashboardInterface::PAGE_REGISTER)
            ->setController(UserRegisterController::class)
            ->setForm($registerationForm)
            ->setTemplate($this->dashboard->useTheme('/pages/user/security/register.html.twig'));
    }

    /**
     * @method createRecoveryPage
     */
    public function createRecoveryPage(): PageManager
    {
        $recoveryForm = new UserRecoveryForm(UserDashboardInterface::PAGE_RECOVERY);

        return $this->createPage(UserDashboardInterface::PAGE_RECOVERY)
            ->setController(UserRecoveryController::class)
            ->setForm($recoveryForm)
            ->setTemplate($this->dashboard->useTheme('/pages/user/security/recovery.html.twig'));
    }

    /**
     * @method createLogoutPage
     */
    public function createLogoutPage(): PageManager
    {
        $logoutNavigation = [
            'label' => 'logout',
            'href' => $this->dashboard->urlGenerator('/' . UserDashboardInterface::PAGE_LOGOUT),
            'icon' => 'bi bi-power',
            'order' => 1024
        ];

        return $this->createPage(UserDashboardInterface::PAGE_LOGOUT, false)
            ->setController(UserLogoutController::class)
            ->addMenuItem(
                UserDashboardInterface::PAGE_LOGOUT, 
                $logoutNavigation, 
                $this->dashboard->userMenu
            )
            ->setCustom('endpoint', $this->dashboard->urlGenerator('/'));
    }
    
    /**
     * @method createIndexPage
     */
    public function createIndexPage(): PageManager
    {
        $dashboardNavigation = new TreeNode('dashboard', [
            'label' => 'dashboard',
            'href' => $this->dashboard->urlGenerator('/'),
            'icon' => 'bi bi-speedometer',
        ]);

        return $this->createPage(UserDashboardInterface::PAGE_INDEX)
            ->setRoute('/')
            ->setController(UserIndexController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/user/index.html.twig'))
            ->addMenuItem(
                UserDashboardInterface::PAGE_INDEX, 
                $dashboardNavigation, 
                $this->dashboard->menu
            );
    }

    /**
     * @method createNotificationPage
     */
    public function createNotificationPage(): PageManager
    {
        return $this->createPage(UserDashboardInterface::PAGE_NOTIFICATIONS)
            ->setController(UserNotificationController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/notifications.html.twig'));
    }

    /**
     * @method createUserProfilePage
     */
    public function createUserProfilePage(): PageManager
    {
        $profileNavigation = [
            'label' => 'Profile',
            'href' => $this->dashboard->urlGenerator('/' . UserDashboardInterface::PAGE_USER_PROFILE),
            'icon' => 'bi bi-person'
        ];
        
        $profilePillNavigation = [
            'label' => 'Profile',
            'href' => $this->dashboard->urlGenerator('/' . UserDashboardInterface::PAGE_USER_PROFILE),
            'icon' => 'bi bi-person-circle',
        ];
        
        return $this->createPage(UserDashboardInterface::PAGE_USER_PROFILE)
            ->setController(UserProfileController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/user/profile/main.html.twig'))
            ->addMenuItem(
                UserDashboardInterface::PAGE_USER_PROFILE, 
                $profileNavigation, 
                $this->dashboard->menu
            )
            ->addMenuItem(
                'profile-batch-profile', 
                $profilePillNavigation, 
                $this->dashboard->profileBatch
            );
    }

    /**
     * @method createUserPasswordPage
     */
    public function createUserPasswordPage(): PageManager
    {
        $passwordPillNavigation = [
            'label' => 'password',
            'href' => $this->dashboard->urlGenerator('/' . UserDashboardInterface::PAGE_USER_PASSWORD),
            'icon' => 'bi bi-unlock'
        ];

        return $this->createPage(UserDashboardInterface::PAGE_USER_PASSWORD)
            ->setController(UserPasswordController::class)
            ->setTemplate($this->dashboard->useTheme('/pages/user/profile/password.html.twig'))
            ->addMenuItem(
                'profile-batch-password', 
                $passwordPillNavigation, 
                $this->dashboard->profileBatch
            );
    }
}