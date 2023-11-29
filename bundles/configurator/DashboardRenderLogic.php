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
    ) {
        $this->user = new User();
        $this->user->getFromSession();
        $this->isLoggedIn = $this->user->exists();
    }

    /**
     * @method eventAction
     */
    public function eventAction(array|object $data): void
    {
        if(!$this->isLoggedIn && $this->dashboard->isFirewallEnabled()) {
            $this->displayLoginPage();
        };
        $this->evalUserPermission();
        $this->createUserInterface();
    }

    /**
     * @method displayLoginPage
     */
    protected function displayLoginPage(): void
    {
        $loginPageManager = $this->dashboard->pageRepository->getPageManager(PageManager::LOGIN);
        $loginForm = $loginPageManager->getForm();
        $loginForm->handleSubmission();
        $this->isLoggedIn = (bool)$this->user->getFromSession();
        if(!$this->isLoggedIn) {
            $this->template = $loginPageManager->getTemplate();
            $this->options['form'] = $loginForm;
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
        $this->remodelMenu($this->dashboard->menu->children);
        $dashboardExtension = new DashboardExtension($this->dashboard);
        $this->uss->addTwigExtension($dashboardExtension);
        $this->setJSVariables();
        $this->options['_theme'] = '@Theme/' . $this->dashboard->config->getTheme();
        $this->options['user'] = $this->user;
        $this->uss->render($this->template, $this->options);
    }

    /**
     * @method refactorMenuItems
     */
    protected function remodelMenu(array $children): void
    {
        foreach($children as $item) {
            if($item->getAttr('pinned')) {
                $item->setAttr('active', $item->parentNode->getAttr('active'));
            }
            if($item->getAttr('active') ?? false) {
                $parentNode = $item->parentNode;
                while($parentNode && $parentNode->level) {
                    $parentNode->setAttr('expanded', true);
                    $parentNode = $parentNode->parentNode;
                }
            }
            if(!empty($item->children)) {
                $this->remodelMenu($item->children);
            }
        }
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
