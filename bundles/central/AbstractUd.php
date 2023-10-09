<?php

use Ucscode\SQuery\SQuery;

/**
 * @author Uchenna Ajah <uche23mail@gmail.com>
 */
abstract class AbstractUd extends AbstractUdBase
{
    protected bool $isRecursiveRender = false;

    public function setConfig(?string $property = null, mixed $value = null): void
    {
        $this->configs[$property] = $value;
    }

    public function getConfig(?string $property = null): mixed
    {
        if(is_null($property)) {
            return $this->configs;
        };
        return $this->configs[$property] ?? null;
    }

    public function removeConfig(string $property): void
    {
        if(array_key_exists($property, $this->configs)) {
            unset($this->configs[$property]);
        };
    }

    public function addArchive(UdArchive $archive): void
    {
        $this->archives[] = $archive;
    }

    public function getArchive(string $archiveName): ?UdArchive
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
        $urlGenerator = new UrlGenerator($archive->get('route'));
        return $urlGenerator->getResult();
    }

    public function enableFirewall(bool $enable = true): void
    {
        $this->firewallEnabled = $enable;
    }

    public function render(string $template, array $options = []): void
    {
        $user = new User();
        if(!$user->getFromSession() && $this->firewallEnabled) {
            //$this->activateLoginarchive($user, $template, $options);
        };
        $options['user'] = $user;
        Uss::instance()->render($template, $options);
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

}
