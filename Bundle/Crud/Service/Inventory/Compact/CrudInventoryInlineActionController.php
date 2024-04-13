<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

class CrudInventoryInlineActionController
{
    protected bool $nonceApproved = false; // capture from $_GET request
    protected ?int $entityId = null;

    public function __construct(protected CrudInventoryInterface $crudInventory)
    {
        $this->checkNonceApproval();
        $this->getEntityId();
        
            if($this->crudInventory->getChannel() === CrudEnum::DELETE) {
                $this->crudInventory->getInlineAction('inventory:delete') && 
                $this->entityId &&
                $this->deleteEntityOnRequest();
            }
        }

    protected function checkNonceApproval(): void
    {
        $nonce = $_GET['nonce'] ?? false;
        if($nonce) {
            $entry = $_SESSION[UssImmutable::APP_SESSION_KEY];
            $this->nonceApproved = Uss::instance()->nonce($entry, $nonce);
        }
    }

    protected function getEntityId(): void
    {
        $entityId = $_GET['entity'] ?? null;
        !is_numeric($entityId) ?: $this->entityId = (int)$entityId;
    }

    protected function deleteEntityOnRequest(): void
    {
        $crudEditor = new CrudEditor($this->crudInventory->tableName);
        $crudEditor->setEntityPropertiesByOffset($this->entityId);
        if($crudEditor->isEntityInDatabase()) {
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