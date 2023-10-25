<?php

abstract class AbstractDashboard extends AbstractDashboardComposition
{
    public function isActiveBase(): bool
    {
        $uss = Uss::instance();
        $regex = '#^' . $this->config->getBase() . '(?!\w)#is';
        $request = $uss->filterContext($uss->splitUri());
        return preg_match($regex, $request);
    }

    public function getArchiveUrl(string $name): ?string
    {
        $ar = new ArchiveRepository($this::class);
        $archive = $ar->getArchive($name);
        if(!$archive) {
            return null;
        }
        $urlGenerator = $this->urlGenerator($archive->getRoute() ?? '');
        return $urlGenerator->getResult();
    }

    public function urlGenerator(string $path = '/', array $query = []): UrlGenerator
    {
        $urlGenerator = new UrlGenerator($path, $query, $this->config->getBase());
        return $urlGenerator;
    }

    public function setAttribute(?string $property = null, mixed $value = null): void
    {
        $this->attributes[$property] = $value;
    }

    public function getAttribute(?string $property = null): mixed
    {
        if(is_null($property)) {
            return $this->attributes;
        };
        return $this->attributes[$property] ?? null;
    }

    public function removeAttribute(string $property): void
    {
        if(array_key_exists($property, $this->attributes)) {
            unset($this->attributes[$property]);
        };
    }

    public function enableFirewall(bool $enable = true): void
    {
        $this->firewallEnabled = $enable;
    }

    public function render(string $template, array $options = []): void
    {
        Event::instance()->addListener('dashboard:render', function () use (&$template, &$options) {
            $uss = Uss::instance();
            $options['user'] = new User();
            $uss->addTwigExtension(new DashboardTwigExtension($this));
            if(!$options['user']->getFromSession() && $this->firewallEnabled) {
                $this->renderLoginArchive($template, $options);
            };
            $this->javaScriptInfo($options['user']);
            $options['_theme'] = '@Theme/' . $this->config->getTheme();
            $uss->render($template, $options);
        });
    }

    /**
     * Parameters are passed by reference I.E The original value and not a copy
     * @method renderLoginArchive
     */
    protected function renderLoginArchive(string &$template, array &$options): void
    {
        $archive = $this->archiveRepository->getArchive(Archive::LOGIN);
        $loginFormClass = $archive->getForm();

        // Handle Login Request
        $formInstance = new $loginFormClass(Archive::LOGIN);
        $formInstance->handleSubmission();

        // Check again if the user login session was successful
        $user = $options['user']->getFromSession();

        if(!$user) {
            $template = $archive->getTemplate();
            $options['form'] = $formInstance;
        };
    }

    protected function useTheme(string $template): string
    {
        $theme = $this->config->getTheme();
        $dymanicTemplate = "@Theme/{$theme}/{$template}";
        return Uss::instance()->filterContext($dymanicTemplate);
    }

    protected function javaScriptInfo(User $user): void
    {
        $uss = Uss::instance();
        $uss->addJsProperty('dashboard', [
            'url' => $this->urlGenerator()->getResult(),
            'nonce' => $uss->nonce('Ud'),
            'loggedIn' => $user->exists()
        ]);
    }
}
