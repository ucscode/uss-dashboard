<?php

class DashboardRenderLogic implements EventInterface
{
    protected User $user;
    protected bool $isLoggedIn;

    public function __construct(
        protected DashboardInterface $dashboard,
        protected Uss $uss,
        protected string $template,
        protected array $options
    ){
        $this->user = new User();
        $this->user->getFromSession();
        $this->isLoggedIn = $this->user->exists();
    }

    /**
     * @method eventAction
     */
    public function eventAction(array $data): void
    {
        if(!$this->isLoggedIn && $this->dashboard->isFirewallEnabled()) {
            $this->enableLoginArchive();
        };
        $this->evalUserPermission();
        $this->createUserInterface();
    }

    /**
     * @method enableLoginArchive
     */
    protected function enableLoginArchive(): void
    {
        $loginArchive = $this->dashboard->archiveRepository->getArchive(Archive::LOGIN);
        $loginFormClass = $loginArchive->getForm();
        $formInstance = new $loginFormClass(Archive::LOGIN);
        $formInstance->handleSubmission();
        /**
         * Check again if the login was successful
         * Otherwise, redirect user to login page
         */
        $this->isLoggedIn = (bool)$this->user->getFromSession();
        if(!$this->isLoggedIn) {
            $this->template = $loginArchive->getTemplate();
            $this->options['form'] = $formInstance;
        };
    }

    /**
     * @method evaluateRestrictions
     */
    protected function evalUserPermission(): void
    {
        if($this->isLoggedIn) {
            $permissions = $this->dashboard->config->getPermissions();
            $roles = $this->user->getUserMeta('user.roles');
            $matchingRoles = array_intersect($permissions, $roles);

            if(empty($matchingRoles)) {
                $template = $this->dashboard->config->getPermissionDeniedTemplate();
                $this->template = $this->dashboard->useTheme($template);
            };
        };
    }

    /**
     * @method createUserInterface
     */
    protected function createUserInterface(): void
    {
        $dashboardExtension = new DashboardTwigExtension($this->dashboard);
        $this->uss->addTwigExtension($dashboardExtension);
        $this->setJSVariables();
        $this->options['_theme'] = '@Theme/' . $this->dashboard->config->getTheme();
        $this->options['user'] = $this->user;
        $this->uss->render($this->template, $this->options);
    }

    /**
     * @method setJSVariables()
     */
    protected function setJSVariables(): void
    {
        $this->uss->addJsProperty('dashboard', [
            'url' => $this->dashboard->urlGenerator()->getResult(),
            'nonce' => $this->uss->nonce('Ud'),
            'loggedIn' => $this->isLoggedIn
        ]);
    }
}