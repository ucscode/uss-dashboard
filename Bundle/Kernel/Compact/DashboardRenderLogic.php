<?php

namespace Module\Dashboard\Bundle\Kernel\Compact;

use Exception;
use Module\Dashboard\Bundle\Extension\DashboardExtension;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\User\User;
use Uss\Component\Event\EventInterface;
use Uss\Component\Kernel\Resource\Enumerator;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

final class DashboardRenderLogic implements EventInterface
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
        !$this->isLoggedIn &&
        $this->dashboard->isFirewallEnabled() ?
            $this->displayLoginPage() : null;

        $this->examineUserPermission();
        $this->createUserInterface();
    }

    /**
     * @method displayLoginPage
     */
    protected function displayLoginPage(): void
    {
        $loginDocument = $this->dashboard->getDocument('login');

        if($loginDocument) {
            $loginForm = $loginDocument->getCustom('app.form');

            if($loginForm instanceof DashboardFormInterface) {
                $loginForm->build(); //
                $loginForm->handleSubmission(); //

                $this->isLoggedIn =
                    $this->user
                        ->acquireFromSession()
                        ->isAvailable();

                if(!$this->isLoggedIn) {
                    $this->template = $loginDocument->getTemplate();
                    $this->options['form'] = $loginForm;
                };

                return;
            }

            throw new Exception("Dashboard application login form must be an instance of " . DashboardFormInterface::class);

        }

        throw new Exception("Unable to get 'login' document instance for " . $this->dashboard::class);
    }

    /**
     * @method evaluateRestrictions
     */
    protected function examineUserPermission(): void
    {
        if($this->isLoggedIn) {

            $this->user->setLastSeen(new \DateTime());
            $this->user->persist();

            $permissions = $this->dashboard->appControl->getPermissions();
            $roles = $this->user->meta->get('user.roles');
            $matchingRoles = array_intersect($permissions, $roles ?? []);

            if(empty($matchingRoles)) {
                $error403 = 'pages/error/403.html.twig';
                if(is_file($this->dashboard->getTheme($error403, Enumerator::FILE_SYSTEM))) {
                    $this->template = $this->dashboard->getTheme($error403);
                    return;
                }
                $this->template = 'error.html.twig';
                $this->options['image'] = $this->uss->pathToUrl(UssImmutable::ASSETS_DIR . "/images/errors/403.webp");
            };

        };
    }

    /**
     * @method createUserInterface
     */
    protected function createUserInterface(): void
    {
        //$this->remodelMenu($this->dashboard->menu->children);
        $this->uss->twig->addExtension(new DashboardExtension($this->dashboard));
        $this->uss->templateContext['user'] = $this->options['user'] = $this->user;

        $this->uss->jsCollection['dashboard'] = [
            'url' => $this->dashboard->urlGenerator()->getResult(),
            'nonce' => $this->uss->nonce($_SESSION[UssImmutable::APP_SESSION_KEY]),
            'loggedIn' => $this->isLoggedIn
        ];
        
        Flash::instance()->dump();

        $this->uss->render($this->template, $this->options);
    }
}
