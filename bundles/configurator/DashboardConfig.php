<?php

final class DashboardConfig
{
    protected string $base;
    protected string $theme;
    protected string $parentTheme = 'default';
    protected array $permissions = [];
    protected string $permissionDeniedTemplate = '403.html.twig';

    /**
     * @method setBase
     */
    public function setBase(string $base): self
    {
        $uss = Uss::instance();
        $this->base = $uss->filterContext($base);
        return $this;
    }

    /**
     * @method getBase
     */
    public function getBase(): string
    {
        return $this->base;
    }

    /**
     * @method setTheme
     */
    public function setTheme(string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @method getTheme
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @method setParentTheme
     */
    public function  setParentTheme(string $parentTheme): self
    {
        $this->parentTheme = $parentTheme;
        return $this;
    }

    /**
     * @method getParentTheme
     */
    public function getParentTheme(): string 
    {
        return $this->parentTheme;
    }

    /**
     * @method setPermissions
     */
    public function setPermissions(array $permissions): self
    {
        $this->permissions = array_unique(array_values($permissions));
        return $this;
    }

    /**
     * @method getPermissions
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @method addPermission
     */
    public function addPermission(string $permission): self
    {
        if(!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission;
        };
        return $this;
    }

    /**
     * @method removePermission
     */
    public function removePermission(string $permission): self
    {
        $key = array_search($permission, $this->permissions, true);
        if($key !== false) {
            unset($this->permissions[$key]);
            $this->permissions = array_values($this->permissions);
        }
        return $this;
    }

    /**
     * @method setPermissionDeniedTemplate
     */
    public function setPermissionDeniedTemplate(string $template): self
    {
        $this->permissionDeniedTemplate = $template;
        return $this;
    }

    /**
     * @method getPermissionDeniedTemplate
     */
    public function getPermissionDeniedTemplate(): string
    {
        return $this->permissionDeniedTemplate;
    }
}
