<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

class CrudInventoryActionControl
{
    protected CrudEnum $channel;
    protected bool $nonceApproved = false;
    protected ?int $entityId = null;

    public function __construct(protected CrudInventoryInterface $crudInventory)
    {
        $this->getChannel();
        $this->getNonceApproval();
        $this->getEntityId();
        if($this->channel === CrudEnum::DELETE) {
            $this->deleteEntityOnRequest();
        }
    }

    protected function getChannel(): void
    {
        $this->channel = match($_GET['channel'] ?? null) {
            CrudEnum::CREATE->value => CrudEnum::CREATE,
            CrudEnum::DELETE->value => CrudEnum::DELETE,
            CrudEnum::UPDATE->value => CrudEnum::UPDATE,
            default => CrudEnum::READ
        };
    }

    protected function getNonceApproval(): void
    {
        $nonce = $_GET['nonce'] ?? false;
        if($nonce) {
            $entry = $_SESSION[UssImmutable::SESSION_KEY];
            $this->nonceApproved = Uss::instance()->nonce($entry, $nonce);
        }
    }

    protected function getEntityId(): void
    {
        $entityId = $_GET['entity'] ?? null;
        if(is_numeric($entityId)) {
            $this->entityId = (int)$entityId;
        }
    }

    protected function deleteEntityOnRequest(): void
    {
        $hasInlineDeleteAction = $this->crudInventory->getInlineAction('inventory:delete');
        if($hasInlineDeleteAction && $this->entityId) {
            $crudEditor = new CrudEditor($this->crudInventory->tableName);
            $crudEditor->setEntityByOffset($this->entityId);
            $entityExists = $crudEditor->isEntityInDatabase();
            if($entityExists) {
                if($this->nonceApproved) {
                    $crudEditor->deleteEntity();
                    return;
                }
                $toast = (new Toast())
                    ->setBackground(Toast::BG_DANGER)
                    ->setMessage("Invalid security token")
                ;
                Flash::instance()->addToast($toast);
            }
        }
    }
}