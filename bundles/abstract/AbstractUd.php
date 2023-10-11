<?php

use Ucscode\SQuery\SQuery;

abstract class AbstractUd extends AbstractUdBase
{
    public function urlGenerator(string $path = '/', array $query = []): UrlGenerator
    {
        $urlGenerator = new UrlGenerator($path, $this->base);
        foreach($query as $key => $value) {
            $urlGenerator->setQuery($key, $value);
        }
        return $urlGenerator;
    }

    public function setStorage(?string $property = null, mixed $value = null): void
    {
        $this->storage[$property] = $value;
    }

    public function getStorage(?string $property = null): mixed
    {
        if(is_null($property)) {
            return $this->storage;
        };
        return $this->storage[$property] ?? null;
    }

    public function removeStorage(string $property): void
    {
        if(array_key_exists($property, $this->storage)) {
            unset($this->storage[$property]);
        };
    }

    public function addArchive(Archive $archive): void
    {
        $this->archives[] = $archive;
    }

    public function getArchive(string $archiveName): ?Archive
    {
        $archives = array_values(array_filter($this->archives, function ($archive) use ($archiveName) {
            return $archive->name === $archiveName;
        }));
        return $archives[0] ?? null;
    }

    public function removeArchive(string $archiveName): null|bool
    {
        $archive = $this->getArchive($archiveName);
        if($archive) {
            if($archive->name === 'login') {
                throw new \Exception(
                    sprintf(
                        "%s Error: Default login archive can only be modified but cannot be removed; Please make changes to the archive attributes instead",
                        __METHOD__
                    )
                );
            }
            $key = array_search($archive, $this->archives, true);
            if($key !== false) {
                unset($this->archives[$key]);
            };
        };
        return null;
    }

    public function getArchiveUrl(string $archiveName): ?string
    {
        $ud = Ud::instance();
        $archive = $ud->getArchive($archiveName);
        if(!$archive || is_null($archive->get('route'))) {
            return null;
        }
        $urlGenerator = $ud->urlGenerator($archive->get('route'));
        return $urlGenerator->getResult();
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

    public function fetchData(string $table, mixed $value, $column = 'id'): ?array
    {
        $parameter = is_iterable($value) ? $value : $column;
        $SQL = (new SQuery())->select()
            ->from($table)
            ->where($parameter, $value);
        $result = Uss::instance()->mysqli->query($SQL);
        return $result->fetch_assoc();
    }

    protected function renderLoginArchive(string $template, array $options): ?User
    {
        $loginPage = $this->getArchive(Archive::LOGIN);
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
