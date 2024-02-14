<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Kernel\Compact\DashboardEnvironment;
use Uss\Component\Kernel\Abstract\AbstractUss;
use Uss\Component\Kernel\Extension\Extension;
use Uss\Component\Kernel\Uss;

abstract class AbstractSandbox extends AbstractUss
{
    public readonly bool $isLocalhost;
    protected Extension $borrowedExtension;

    public function __construct()
    {
        parent::__construct();
        new DashboardEnvironment($this);
        $this->borrowedExtension = new Extension(Uss::instance());
        $this->twig->addExtension($this->borrowedExtension);
        $this->isLocalhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1'], true);
    }
    
    public function render(string $template, array $context = []): string
    {
        $this->borrowedExtension->configureRenderContext();
        $context += Uss::instance()->templateContext;
        return $this->twig->render($template, $context);
    }
}