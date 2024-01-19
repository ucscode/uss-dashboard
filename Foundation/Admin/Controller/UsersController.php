<?php

namespace Module\Dashboard\Foundation\Admin\Controller;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Foundation\Admin\Controller\Users\InventoryController;
use Module\Dashboard\Foundation\Admin\Controller\Users\UpdateController;

class UsersController extends AbstractDashboardController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $channel = trim($_GET['channel'] ?? '');

        $userController = match($channel) {
            CrudEnum::UPDATE->value => new UpdateController($document),
            default => new InventoryController($document),
        };

        $crudKernel = $userController->getComponent();

        $document->setContext([
            'crudKernel' => $crudKernel,
        ]);
    }
}
