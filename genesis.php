<?php

new class () {
    public function __construct() 
    {
        new DatabaseConfigurator();
        User::init();
        $this->configureFilesystem();
        $this->configureUserDashboard();
        $this->configureAdminDashboard();
        $this->configureDashboardOutput();
    }

    protected function configureFilesystem(): void
    {
        $uss = Uss::instance();
        $uss->filesystemLoader->addPath(DashboardImmutable::MAIL_TEMPLATE_DIR, 'Mail');
        $uss->filesystemLoader->addPath(DashboardImmutable::THEME_DIR, 'Theme');
    }

    protected function configureUserDashboard(): void
    {
        $config = (new DashboardConfig())
            ->setBase('/dashboard')
            ->setTheme('default')
            ->addPermission(RoleImmutable::ROLE_USER)
            ->setPermissionDeniedTemplate('/pages/403.html.twig');

        UserDashboard::instance()->createProject($config);
    }
    
    public function configureAdminDashboard(): void
    {
        $config = (new DashboardConfig())
            ->setBase("/admin")
            ->setTheme('default')
            ->setPermissions([
                RoleImmutable::ROLE_SUPERADMIN,
                RoleImmutable::ROLE_ADMIN,
            ])
            ->setPermissionDeniedTemplate('/pages/403.html.twig');

        AdminDashboard::instance()->createProject($config);
    }
    
    protected function configureDashboardOutput(): void
    {
        (new Event())->addListener('modules:loaded', function () {
            Event::emit('dashboard:render');
        }, -9);
    }
};