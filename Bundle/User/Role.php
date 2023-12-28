<?php

namespace Module\Dashboard\Bundle\User;

class Roles
{
    public function __construct(protected User $user)
    {}

    /**
     * @method getRoles
     */
    public function getAll(): array
    {
        return $this->user->meta->get('user.roles') ?? [];
    }

    /**
     * @method setRoles
     */
    public function set(array $roles): bool
    {
        sort($roles);
        $roles = array_unique(array_values(array_filter($roles)));
        return $this->user->meta->set('user.roles', $roles);
    }

    /**
     * @method addRole
     */
    public function add(string $role): bool
    {
        $roles = $this->getAll();
        if(!in_array($role, $roles)) {
            $roles[] = $role;
        }
        sort($roles);
        return $this->user->meta->set('user.roles', array_values($roles));
    }

    /**
     * @method removeRole
     */
    public function remove(string $role): bool
    {
        $roles = $this->getAll();
        $key = array_search($role, $roles, true);
        if($key !== false) {
            unset($roles[$key]);
        }
        return $this->user->meta->set('user.roles', $roles);
    }

    /**
     * @method hasRole
     */
    public function has(string $role): bool
    {
        return in_array($role, $this->getAll(), true);
    }
}