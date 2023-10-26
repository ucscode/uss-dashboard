<?php

abstract class AbstractDashboard extends AbstractDashboardComposition
{
    /**
     * @method isActiveBase
     */
    public function isActiveBase(): bool
    {
        $uss = Uss::instance();
        $regex = '#^' . $this->config->getBase() . '(?!\w)#is';
        $request = $uss->filterContext($uss->splitUri());
        return preg_match($regex, $request);
    }

    /**
     * @method getArchiveUrl
     */
    public function getArchiveUrl(string $name): ?string
    {
        $archive = $this->archiveRepository->getArchive($name);
        if($archive) {
            $urlGenerator = $this->urlGenerator($archive->getRoute() ?? '');
            return $urlGenerator->getResult();
        }
        return null;
    }

    /**
     * @method urlGenerator
     */
    public function urlGenerator(string $path = '/', array $query = []): UrlGenerator
    {
        $urlGenerator = new UrlGenerator($path, $query, $this->config->getBase());
        return $urlGenerator;
    }

    /**
     * @method setAttribute
     */
    public function setAttribute(?string $property = null, mixed $value = null): void
    {
        $this->attributes[$property] = $value;
    }

    /**
     * @method getAttribute
     */
    public function getAttribute(?string $property = null): mixed
    {
        if(is_null($property)) {
            return $this->attributes;
        };
        return $this->attributes[$property] ?? null;
    }

    /**
     * @method removeAttribute
     */
    public function removeAttribute(string $property): void
    {
        if(array_key_exists($property, $this->attributes)) {
            unset($this->attributes[$property]);
        };
    }

    /**
     * @method enableFirewall
     */
    public function enableFirewall(bool $enable = true): void
    {
        $this->firewallEnabled = $enable;
    }

    /**
     * @method render
     */
    public function render(string $template, array $options = []): void
    {
        (new Event())->addListener(
            'dashboard:render', 
            new class($this, Uss::instance(), $template, $options) implements EventInterface 
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
                    if(!$this->isLoggedIn && $this->dashboard->firewallEnabled) {
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
                            $archive = $this->dashboard->archiveRepository->getArchive('restriction');
                            if($archive) {
                                $template = $archive->getTemplate();
                            } else {
                                throw new \Exception(
                                    "Restricted: 403 Template not found"
                                );
                            }
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
        );
    }

    protected function useTheme(string $template): string
    {
        $theme = $this->config->getTheme();
        $dymanicTemplate = "@Theme/{$theme}/{$template}";
        return Uss::instance()->filterContext($dymanicTemplate);
    }
}
