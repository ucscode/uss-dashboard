<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Compact\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\User\Interface\UserInterface;

class UpdateController extends AbstractUsersController
{
    protected CrudEditor $crudEditor;

    protected function composeMicroApplication(): void
    {
        $this->crudEditor = new CrudEditor(UserInterface::USER_TABLE);
        $this->crudEditor->setEntityByOffset($_GET['entity'] ?? '');
    }

    public function getComponent(): CrudKernelInterface
    {
        return $this->crudEditor;
    }
}