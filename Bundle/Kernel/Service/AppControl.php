<?php

namespace Module\Dashboard\Bundle\Kernel\Service;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Service\Interface\AppControlInterface;
use Symfony\Component\Yaml\Yaml;
use Uss\Component\Kernel\Uss;

class AppControl implements AppControlInterface
{
    protected string $base;
    protected string $themeFolder;
    protected array $permissions = [];

    /**
     * @method setBase
     */
    public function setUrlBasePath(string $base): self
    {
        $this->base = Uss::instance()->filterContext($base);
        return $this;
    }

    /**
     * @method getBase
     */
    public function getUrlBasePath(): string
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
     * @method getThemeConfig
     */
    public function getThemeConfig(): array
    {
        $filePlaceholder = DashboardImmutable::THEMES_DIR . "/%s/theme.%s";
        $configFile = sprintf($filePlaceholder, $this->getThemeFolder(), 'yml');
        is_file($configFile) ?: $configFile = sprintf($filePlaceholder, $this->getThemeFolder(), 'yaml');
        return is_file($configFile) ? Yaml::parseFile($configFile) : [];
    }
}
