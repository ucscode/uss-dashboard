<?php

namespace Module\Dashboard\Bundle\Kernel\Service;

use Uss\Component\Kernel\Uss;

final class AppControl
{
    protected string $base;
    protected string $themeFolder;
    protected array $permissions = [];
    protected ?string $permissionDeniedTemplate = null;

    /**
     * @method setBase
     */
    public function setBase(string $base): self
    {
        $this->base = Uss::instance()->filterContext($base);
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
    public function setThemeFolder(string $theme): self
    {
        $this->themeFolder = Uss::instance()->filterContext($theme);
        return $this;
    }

    /**
     * @method getTheme
     */
    public function getThemeFolder(): string
    {
        return $this->themeFolder;
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
    public function getPermissionDeniedTemplate(): ?string
    {
        return $this->permissionDeniedTemplate;
    }
}
