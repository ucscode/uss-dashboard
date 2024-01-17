<?php

namespace Module\Dashboard\Foundation\System\Compact\Abstract;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;

abstract class AbstractDocumentFactory
{
    protected string $base;

    public function __construct(protected DashboardInterface $dashboard, protected string $namespace)
    {
        $this->base = $this->dashboard->appControl->getBase();
    }

}