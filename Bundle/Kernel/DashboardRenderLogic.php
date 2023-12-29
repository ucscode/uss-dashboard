<?php

namespace Module\Dashboard\Bundle\Kernel;

use Module\Dashboard\Bundle\Alert\Alert;
use Module\Dashboard\Bundle\Extension\DashboardExtension;
use Module\Dashboard\Bundle\User\User;
use Uss\Component\Event\EventInterface;
use Uss\Component\Kernel\Uss;

class DashboardRenderLogic implements EventInterface
{
    protected Uss $uss;
    protected User $user;
    protected bool $isLoggedIn;

    public function __construct(
        protected DashboardInterface $dashboard,
        protected string $template,
        protected array $options
    ) {
        $this->uss = Uss::instance();
        $this->user = (new User())->acquireFromSession();
        $this->isLoggedIn = $this->user->isAvailable();
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
        $loginDocument = $this->dashboard->getDocument('login');
        $loginForm = $loginDocument->getCustom('login:form');
        //$loginForm->handleSubmission();
        // try again
        $this->user->acquireFromSession();
        $this->isLoggedIn = $this->user->isAvailable();
        if(!$this->isLoggedIn) {
            $this->template = $loginDocument->getTemplate();
            $this->options['form'] = $loginForm;
        };
    }

    /**
     * @method evaluateRestrictions
     */
    protected function evalUserPermission(): void
    {
        if($this->isLoggedIn) {
            $permissions = $this->dashboard->appControl->getPermissions();
            $roles = $this->user->meta->get('user.roles');
            $matchingRoles = array_intersect($permissions, $roles);
            if(empty($matchingRoles)) {
                $this->template =
                    $this->dashboard->appControl->getPermissionDeniedTemplate() ?:
                    $this->dashboard->getTheme('403.html.twig');
            };
        };
    }

    /**
     * @method createUserInterface
     */
    protected function createUserInterface(): void
    {
        //$this->remodelMenu($this->dashboard->menu->children);
        $this->uss->twigEnvironment->addExtension(new DashboardExtension($this->dashboard));

        $this->options['user'] = $this->user;

        $this->uss->jsCollection['dashboard'] = [
            'url' => $this->dashboard->urlGenerator()->getResult(),
            'nonce' => $this->uss->nonce('__dashboard'),
            'loggedIn' => $this->isLoggedIn
        ];

        Alert::exportContent();

        $this->uss->render($this->template, $this->options);
    }
}
