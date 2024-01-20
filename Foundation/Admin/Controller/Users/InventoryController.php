<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Compact\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\Crud\Service\Inventory\CrudInventory;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Uss\Component\Kernel\Uss;

class InventoryController extends AbstractUsersController
{
    protected CrudInventory $crudInventory;

    protected function composeMicroApplication(): void
    {
        $this->crudInventory = new CrudInventory(UserInterface::USER_TABLE);
        $this->crudInventory->setTableBackgroundWhite();
        $this->regulateColumns();
    }

    public function getCrudKernel(): CrudKernelInterface
    {
        return $this->crudInventory;
    }

    protected function regulateColumns(): void
    {
        $uss = Uss::instance();
        $omitFields = ['id', 'password'];
        if(empty($uss->options->get('user:collect-username'))) {
            $omitFields[] = 'username';
        };
        foreach($omitFields as $column) {
            $this->crudInventory->removeColumn($column);
        }
    }
}