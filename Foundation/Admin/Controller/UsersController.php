<?php

namespace Module\Dashboard\Foundation\Admin\Controller;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Crud\Service\Inventory\CrudInventory;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\UssElement\UssElement;

class UsersController extends AbstractDashboardController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $inventory = new CrudInventory(UserInterface::USER_TABLE);
        //$inventory->disableGlobalActions();
        //$inventory->disableInlineAction();
        // var_dump($_POST);
        $document->setContext([
            'inventory' => $inventory,
        ]);
    }
}
