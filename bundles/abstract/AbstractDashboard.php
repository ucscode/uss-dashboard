<?php

use Ucscode\SQuery\SQuery;

abstract class AbstractDashboard extends AbstractDashboardComposition
{
    public function getArchiveUrl(string $name): ?string
    {
        $ar = new ArchiveRepository($this::class);
        $archive = $ar->getArchive($name);
        if(!$archive || is_null($archive->get('route'))) {
            return null;
        }
        $urlGenerator = $this->urlGenerator($archive->get('route'));
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
        Event::instance()->addListener('modules:loaded', function () use (&$template, &$options) {
            $options['user'] = new User();
            if(!$options['user']->getFromSession() && $this->firewallEnabled) {
                $option['user'] = $this->renderLoginArchive($template, $options);
            };
            Uss::instance()->render($template, $options);
        });
    }

    protected function renderLoginArchive(string $template, array $options): ?User
    {
        $ar = new ArchiveRepository($this::class);
        $loginPage = $ar->getArchive(Archive::LOGIN);
        $loginForm = $loginPage->get('form');

        $form = new $loginForm(Archive::LOGIN);
        $form->handleSubmission();

        $user = $options['user']->getFromSession();

        if(!$user) {
            $template = $loginPage->get('template');
            $options['form'] = $form;
            Uss::instance()->render($template, $options);
        };

        return $user;

    }

}
