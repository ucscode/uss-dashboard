<?php

namespace Module\Dashboard\Foundation\Admin\Controller;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\Admin\Controller\Users\CreateController;
use Module\Dashboard\Foundation\Admin\Controller\Users\Interface\UserControllerInterface;
use Module\Dashboard\Foundation\Admin\Controller\Users\InventoryController;
use Module\Dashboard\Foundation\Admin\Controller\Users\UpdateController;
use Uss\Component\Kernel\Uss;

class UsersController extends AbstractDashboardController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $channel = trim($_GET['channel'] ?? '');

        $userController = match($channel) {
            CrudEnum::CREATE->value => new CreateController($document, $dashboard),
            CrudEnum::UPDATE->value => new UpdateController($document, $dashboard),
            default => new InventoryController($document, $dashboard),
        };
        
        $updatedContext = $this->rewriteContext($channel, $userController, $document->getContext());
        $document->setContext($updatedContext);
    }

    protected function rewriteContext(string $channel, ?UserControllerInterface $userController, array $context): array
    {
        $client = $userController->getClient();
        
        $localContext = [
            'channel' => $channel,
            'crudKernel' => $userController->getCrudKernel(),
            'form' => $userController->getForm(),
            'client' => $client,
            'hint' => $client ? $this->getHints($client) : null,
        ];
        
        return $localContext + $context;
    }

    protected function getHints(User $client): array
    {
        $uss = Uss::instance();
        $roles = $client->roles->getAll();

        if(!empty($roles)) {
            $rolesContent = implode(' ', array_map(function($role) {
                return sprintf("<span class='%s'>%s</span>", 'badge text-bg-secondary', $role);
            }, $roles));
        } else {
            $rolesContent = "<span class='text-danger small'>
                <i class='bi bi-x-lg me-1'></i>No Role Assigned
            </span>";
        }

        $parent = $client->getParent(true);

        $hints = [
            'clientUsername' => $client->getUsername() ?? ($client->isAvailable() ? '* Anonymous' : '* Newbie'),
            'shapes' => str_repeat('&clubs; ', 4),
            'roles' => $rolesContent,
            'parent' => [
                'identity' => $parent ? $parent->getEmail() : 'No One',
                'href' => !$parent ? 'javascript:void(0)' : $uss->replaceUrlQuery([
                    'entity' => $parent->getId(),
                    'channel' => CrudEnum::UPDATE->value
                ]),
                'code' => $parent ? $parent->getUsercode() : null,
            ],
        ];
        
        return $hints;
    }
}
