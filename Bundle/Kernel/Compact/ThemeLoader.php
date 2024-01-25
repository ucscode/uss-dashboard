<?php

namespace Module\Dashboard\Bundle\Kernel\Compact;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardThemeInterface;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

class ThemeLoader
{
    protected string $baseClass = 'Theme';
    protected string $themeFolder;
    protected string $themeName;
    protected string $themeFile;
    protected string $namespace;
    protected string $className;

    public function __construct(protected DashboardInterface $dashboard)
    {
        $this->themeFolder = $this->dashboard->appControl->getThemeFolder();
        $this->themeFolder = Uss::instance()->filterContext($this->themeFolder);

        $this->themeFile = sprintf(
            DashboardImmutable::THEMES_DIR . "/%s/%s.php", 
            $this->themeFolder, 
            $this->baseClass
        );

        if(is_file($this->themeFile)) {
            $this->configureProperties();
            $this->instantiateClass();
        }
    }

    protected function configureProperties(): void
    {
        $this->fetchThemeName();
        $path = str_replace(UssImmutable::MODULES_DIR, '', DashboardImmutable::THEMES_DIR);
        $this->namespace = Uss::instance()->filterContext("Module/" . $path . sprintf("/%s", $this->themeName));
        $this->namespace = str_replace("/", "\\", $this->namespace);
        $this->className = $this->namespace . "\\" . $this->baseClass;
    }

    protected function fetchThemeName(): void
    {
        $conversion = preg_replace("/[^\w]+/", ' ', $this->themeFolder);
        $this->themeName = str_replace(" ", "", $conversion);
    }

    protected function instantiateClass(): void
    {
        require_once $this->themeFile;
        
        if(class_exists($this->className)) {
            $implementations = class_implements($this->className);
            $themeInterface = DashboardThemeInterface::class;

            if(in_array($themeInterface, $implementations)) {
                $themeInstance = new $this->className();
                $themeInstance->onload($this->dashboard);
            }
        }
    }
}