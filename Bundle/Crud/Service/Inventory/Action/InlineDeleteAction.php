<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Action;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractInlineAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\InlineActionInterface;
use Uss\Component\Kernel\Uss;

class InlineDeleteAction extends AbstractInlineAction implements InlineActionInterface
{
    public function foreachItem(array $item, CrudInventoryInterface $crudInventory): Action
    {
        $this->crudInventory = $crudInventory;

        $action = new Action();
        $action
            ->addClass("btn-danger")
            ->setAttribute('href', Uss::instance()->replaceUrlQuery([
                'entity' => $item['id'],
                'channel' => CrudEnum::DELETE->value,
            ]))
            ->setAttribute('data-ui-confirm', "Sure you want to delete this data?")
            ->setAttribute("data-ui-size", 'small')
            ->setContent(
                $this->createContent("bi bi-trash", "Delete")
            )
        ;

        return $action;
    }
}