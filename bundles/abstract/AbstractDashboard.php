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
        (new Event())->addListener('dashboard:render', 
            new class($this, Uss::instance(), $template, $options) implements EventInterface 
            {
                protected User $user;

                public function __construct(
                    protected DashboardInterface $dashboard,
                    protected Uss $uss,
                    protected string $template,
                    protected array $options
                ){
                    $this->user = new User();
                    $this->user->getFromSession();
                }

                /**
                 * @method eventAction
                 */
                public function eventAction(array $data): void
                {
                    if(!$this->user->exists() && $this->dashboard->firewallEnabled) {
                        $this->enableLoginArchive();
                    };
                    $this->createUserInterface();
                }

                /**
                 * @method enableLoginArchive
                 */
                protected function enableLoginArchive(): void
                {
                    $archive = $this->dashboard->archiveRepository->getArchive(Archive::LOGIN);
                    $loginFormClass = $archive->getForm();
                    $formInstance = new $loginFormClass(Archive::LOGIN);
                    $formInstance->handleSubmission();
                    if(!$this->user->getFromSession()) {
                        $this->template = $archive->getTemplate();
                        $this->options['form'] = $formInstance;
                    };
                }

                /**
                 * @method createUserInterface
                 */
                protected function createUserInterface(): void
                {
                    //$this->evalutatePermission($template, $options);
                    $dashboardExtension = new DashboardTwigExtension($this->dashboard);
                    $this->uss->addTwigExtension($dashboardExtension);
                    $this->updateJavascript();
                    $this->options['_theme'] = '@Theme/' . $this->dashboard->config->getTheme();
                    $this->options['user'] = $this->user;
                    $this->uss->render($this->template, $this->options);
                }

                /**
                 * @method evaluateRestrictions
                 */
                protected function evalutatePermission(&$template, &$options): void
                {
                    $user = $options['user'];
                    if($user->exists()) {
            
                        $dashboardPermission = $this->dashboard->config->getPermissions();
                        $userRoles = $user->getUserMeta('user.roles');
                        $authorities = array_intersect($dashboardPermission, $userRoles);
            
                        if(empty($authorities)) {
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

                protected function updateJavascript(): void
                {
                    $this->uss->addJsProperty('dashboard', [
                        'url' => $this->dashboard->urlGenerator()->getResult(),
                        'nonce' => $this->uss->nonce('Ud'),
                        'loggedIn' => $this->user->exists()
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
