<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\Crud\Service\Inventory\CrudInventory;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractUsersController;
use Module\Dashboard\Foundation\Admin\Controller\Users\Tool\EntityMutator;
use Ucscode\UssForm\Form\Form;
use Uss\Component\Kernel\Uss;

class InventoryController extends AbstractUsersController
{
    protected CrudInventory $crudInventory;

    protected function composeMicroApplication(): void
    {
        $this->enableDocumentMenu('main:users');
        $this->crudInventory = new CrudInventory(UserInterface::USER_TABLE);
        $this->crudInventory->setTableBackgroundWhite();
        $this->crudInventory->setColumn('roles');
        $this->crudInventory->addEntityMutationIterator('primary', new EntityMutator());
        $this->removeSensitiveColumns();
    }

    public function getCrudKernel(): CrudKernelInterface
    {
        return $this->crudInventory;
    }

    public function getForm(): ?Form
    {
        return null;
    }

    public function getClient(): ?User
    {
        return null;
    }

    protected function removeSensitiveColumns(): void
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