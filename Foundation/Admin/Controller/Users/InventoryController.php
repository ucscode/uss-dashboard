<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\Crud\Service\Inventory\CrudInventory;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractUsersController;
use Module\Dashboard\Foundation\Admin\Controller\Users\Tool\EntityMutator;
use Uss\Component\Kernel\Uss;

class InventoryController extends AbstractUsersController
{
    protected CrudInventory $crudInventory;

    public function __construct(array $context)
    {
        parent::__construct($context);
        $this->enableDocumentMenu('main:users');
        $this->configureProperties();
    }

    public function getCrudKernel(): CrudKernelInterface
    {
        return $this->crudInventory;
    }

    protected function configureProperties(): void
    {
        $this->crudInventory = new CrudInventory(UserInterface::TABLE_USER);
        $this->crudInventory->setTableBackgroundWhite();
        $this->crudInventory->setColumn('roles');
        $this->crudInventory->addEntityMutationIterator('primary', new EntityMutator());
        $this->removeSensitiveColumns();
    }

    protected function removeSensitiveColumns(): void
    {
        $uss = Uss::instance();
        $omitFields = ['id', 'password'];
        !empty($uss->options->get('user:collect-username')) ?: $omitFields[] = 'username';
        foreach($omitFields as $column) {
            $this->crudInventory->removeColumn($column);
        }
    }
}