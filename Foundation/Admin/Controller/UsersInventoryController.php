<?php

namespace Module\Dashboard\Foundation\Admin\Controller;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Crud\Service\Inventory\CrudInventory;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Uss\Component\Kernel\Uss;

class UsersInventoryController extends AbstractDashboardController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $crudInventory = new CrudInventory(UserInterface::USER_TABLE);
        $crudInventory->setTableBackgroundWhite();
        $this->regulateColumns($crudInventory);
        $document->setContext([
            'inventory' => $crudInventory,
        ]);
    }

    protected function regulateColumns(CrudInventory $crudInventory): void
    {
        $uss = Uss::instance();
        $omitFields = ['id', 'password'];
        if(empty($uss->options->get('user:collect-username'))) {
            $omitFields[] = 'username';
        };
        foreach($omitFields as $column) {
            $crudInventory->removeColumn($column);
        }
    }
}
