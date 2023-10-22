<?php

class DashboardConfig 
{
    public readonly string $base;
    public readonly string $templateFilesystem;
    public readonly string $namespace;

    public function setBase(string $route): self 
    {
        $uss = Uss::instance();
        $this->base = $uss->filterContext($route);
        return $this;
    }

    public function setTemplateFilesystem(string $filesystem, string $namespace): self
    {
        $this->templateFilesystem = $filesystem;
        $this->namespace = $namespace;
        $uss = Uss::instance();
        $uss->addTwigFilesystem($this->templateFilesystem, $this->namespace);
        return $this;
    }
}