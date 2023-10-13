<?php

class ArchiveRepository {

    private string $group;
    private static array $archives = [];

    public function __construct(string $group)
    {
        if(!array_key_exists($group, self::$archives)) {
            self::$archives[$group] = [];
        }
        $this->group = $group;
    }

    public function addArchive(string $name, Archive $archive): self
    {
        self::$archives[$this->group][$name] = $archive;
        return $this;
    }

    public function getArchive(string $name): ?Archive
    {
        $archive = self::$archives[$this->group][$name] ?? null;
        return $archive;
    }

    public function removeArchive(string $name): self
    {
        if(array_key_exists($name, self::$archives[$this->group])) {
            unset(self::$archives[$this->group][$name]);
        }
        return $this;
    }

    public function getAllArchives(): array
    {
        return self::$archives[$this->group];
    }

}