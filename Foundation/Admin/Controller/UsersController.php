<?php

namespace Module\Dashboard\Foundation\Admin\Controller;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\Admin\Controller\Users\CreateController;
use Module\Dashboard\Foundation\Admin\Controller\Users\InventoryController;
use Module\Dashboard\Foundation\Admin\Controller\Users\UpdateController;
use Uss\Component\Kernel\Uss;

class UsersController extends AbstractDashboardController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $channel = trim($_GET['channel'] ?? '');

        $userController = match($channel) {
            CrudEnum::CREATE->value => new CreateController($document),
            CrudEnum::UPDATE->value => new UpdateController($document),
            default => new InventoryController($document),
        };

        $crudKernel = $userController->getCrudKernel();
        $entity = $crudKernel instanceof CrudEditor ? $crudKernel->getEntity() : [];
        $client = new User($entity['id'] ?? null);

        $context = [
            'channel' => $channel,
            'crudKernel' => $crudKernel,
            'form' => $userController->getForm(),
            'client' => $client,
            'hint' => $this->getHints($client),
        ] + $document->getContext();

        $document->setContext($context);
    }

    protected function getHints(User $client): array
    {
        $uss = Uss::instance();
        $roles = $client->roles->getAll();

        $rolesContext = !empty($roles) ? implode(' ', array_map(function($role) {
            return sprintf("<span class='%s'>%s</span>", 'badge text-bg-secondary', $role);
        }, $roles)) : 
            "<span class='text-danger small'>
                <i class='bi bi-x-lg me-1'></i>No Role Assigned
            </span>";

        $hints = [
            'clientUsername' => $client->getUsername() ?? ($client->isAvailable() ? '* Anonymous' : '* Newbie'),
            'shapes' => str_repeat('&clubs; ', 4),
            'roles' => $rolesContext
        ];
        
        return $hints;
    }
}
