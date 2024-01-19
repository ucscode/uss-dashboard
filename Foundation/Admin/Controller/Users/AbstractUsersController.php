<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Crud\Compact\Interface\CrudKernelInterface;

abstract class AbstractUsersController
{
    abstract public function getComponent(): CrudKernelInterface;
    abstract protected function composeMicroApplication(): void;

    public function __construct(protected Document $document)
    {
        $this->composeMicroApplication();
    }
}