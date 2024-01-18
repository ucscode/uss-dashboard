<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Compact\Abstract\AbstractCrudKernel;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets\SearchWidget;

abstract class AbstractCrudInventoryFactory extends AbstractCrudKernel
{
    protected function createInventoryResources(): void
    {
        $this->setWidget("inventory:search", (new SearchWidget)->getElement());
    }
}