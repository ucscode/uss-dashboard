<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Action;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractInlineAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\InlineActionInterface;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

class InlineDeleteAction extends AbstractInlineAction implements InlineActionInterface
{
    public function foreachItem(array $item, CrudInventoryInterface $crudInventory): Action
    {
        $this->crudInventory = $crudInventory;
        
        $nonce = Uss::instance()->nonce($_SESSION[UssImmutable::SESSION_KEY]);

        $action = new Action();
        $action
            ->addClass("btn-danger")
            ->setAttribute('href', Uss::instance()->replaceUrlQuery([
                'entity' => $item['id'],
                'channel' => CrudEnum::DELETE->value,
                'nonce' => $nonce,
            ]))
            ->setAttribute('data-ui-confirm', "Sure you want to delete this data? <br> The action cannot be reversed!")
            ->setAttribute("data-ui-size", 'small')
            ->setContent(
                $this->createContent("bi bi-trash", "Delete")
            )
        ;

        return $action;
    }
}