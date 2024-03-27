<?php

namespace Module\Dashboard\Bundle\Kernel\Service;

use Symfony\Component\HttpFoundation\ParameterBag;
use Uss\Component\Trait\SingletonTrait;

class DashboardCollection extends ParameterBag 
{
    use SingletonTrait;

    private array $permissions = [];

    public function addPermission(string $permission): self
    {
        !$this->hasPermission($permission) ?: array_push($this->permissions, $permission);
        return $this;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function removePermission(string $permission): self
    {
        if($this->hasPermission($permission)) {
            $key = array_search($permission, $this->permissions, true);
            unset($this->permissions[$key]);
            $this->permissions = array_values($this->permissions);
        }
        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}