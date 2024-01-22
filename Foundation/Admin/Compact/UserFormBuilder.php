<?php

namespace Module\Dashboard\Foundation\Admin\Compact;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormBuilderInterface;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractFieldConstructor;

class UserFormBuilder implements DashboardFormBuilderInterface
{
    protected CrudEditor $crudEditor;
    protected ?User $client;

    public function __construct(AbstractFieldConstructor $userController)
    {
        $this->crudEditor = $userController->getCrudKernel();
        $this->client = $userController->getClient();
    }

    public function onBuild(AbstractDashboardForm $form): void
    {
        if(($_GET['channel'] ?? null) === CrudEnum::UPDATE->value) {
            $this->crudEditor->setEntityByOffset($_GET['entity'] ?? '');
        };

        $this->client = new User($this->crudEditor->getEntity()['id'] ?? null);

    }
}