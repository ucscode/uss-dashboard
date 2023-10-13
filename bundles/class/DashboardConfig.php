<?php

class DashboardConfig
{
    private string $base;
    private string $templateNamespace;
    private string $templateDirectory;

    public function setBase(string $base): self
    {  
        $this->base = $base;
        return $this;
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function setTemplateNamespace(string $templateNamespace): self
    {
        $this->templateNamespace = $templateNamespace;
        return $this;
    }

    public function getTemplateNamespace(): string
    {
        return $this->templateNamespace;
    }

    public function setTemplateDirectory(string $templateDirectory): self
    {
        $this->templateDirectory = $templateDirectory;
        return $this;
    }

    public function getTemplateDirectory(): string
    { 
        return $this->templateDirectory;
    }

}