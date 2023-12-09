<?php

class PageRepository
{
    private string $group;
    private static array $archives = [];

    public function __construct(string $group)
    {
        if(!array_key_exists($group, self::$archives)) {
            self::$archives[$group] = [];
        }
        $this->group = $group;
    }

    public function addPageManager(string $name, PageManager $archive): self
    {
        self::$archives[$this->group][$name] = $archive;
        return $this;
    }

    public function getPageManager(string $name): ?PageManager
    {
        $archive = self::$archives[$this->group][$name] ?? null;
        return $archive;
    }

    public function removePageManager(string $name): self
    {
        if(array_key_exists($name, self::$archives[$this->group])) {
            unset(self::$archives[$this->group][$name]);
        }
        return $this;
    }

    public function getPageManagers(): array
    {
        return self::$archives[$this->group];
    }

}
