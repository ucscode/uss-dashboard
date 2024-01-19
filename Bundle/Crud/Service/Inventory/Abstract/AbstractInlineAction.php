<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;

abstract class AbstractInlineAction
{
    protected CrudInventoryInterface $crudInventory;

    protected function createContent(string $icon, string $text): string
    {
        $className = $this->crudInventory->isInlineActionAsDropdown() ? null : 'd-none d-lg-inline';
        $content = sprintf(
            "<i class='%s'></i> 
            <span class='%s'>%s</span>",
            $icon,
            $className,
            $text
        );
        return $content;
    }
}