<?php

use Ucscode\SQuery\SQuery;

abstract class AbstractDashboard extends AbstractDashboardComposition
{
    public function isActiveBase(): bool
    {
        $uss = Uss::instance();
        $regex = '#^' . $this->config->base . '(?!\w)#is';
        $request = $uss->filterContext($uss->splitUri());
        return preg_match($regex, $request);
    }

    public function getArchiveUrl(string $name): ?string
    {
        $ar = new ArchiveRepository($this::class);
        $archive = $ar->getArchive($name);
        if(!$archive || is_null($archive->getRoute())) {
            return null;
        }
        $urlGenerator = $this->urlGenerator($archive->getRoute());
        return $urlGenerator->getResult();
    }

    public function urlGenerator(string $path = '/', array $query = []): UrlGenerator
    {
        $urlGenerator = new UrlGenerator($path, $query, $this->config->base);
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
        $event = Event::instance();
        $event->addListener('dashboard:render', function () use (&$template, &$options, $event) {
            $options['user'] = new User();
            $options['namespace'] = '@' . $this->config->namespace;
            if(!$options['user']->getFromSession() && $this->firewallEnabled) {
                $this->renderLoginArchive($template, $options);
            };
            Uss::instance()->render($template, $options);
        });
    }

    protected function renderLoginArchive(string $template, array $options): void
    {
        $loginPage = $this->archiveRepository->getArchive(Archive::LOGIN);
        $loginForm = $loginPage->getForm();

        $form = new $loginForm(Archive::LOGIN);
        $form->handleSubmission();

        $user = $options['user']->getFromSession();

        if(!$user) {
            $template = $loginPage->getTemplate();
            $options['form'] = $form;
            Uss::instance()->render($template, $options);
        };
    }

}
