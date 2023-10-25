<?php

final class DashboardConfig
{
    public readonly string $base;
    public readonly string $namespace;
    public readonly string $theme;
    public readonly string $parentTheme;

    public function setBase(string $base): self
    {
        $uss = Uss::instance();
        $this->base = $uss->filterContext($base);
        return $this;
    }

    public function setTheme(string $theme, string $parentTheme = 'default'): self
    {
        $this->theme = $theme;
        $this->parentTheme = $parentTheme;
        return $this;
    }
}
