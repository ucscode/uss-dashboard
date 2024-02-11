<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory;

use Module\Dashboard\Bundle\Crud\Component\CrudWidgetManager;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractCrudInventory_Level2;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\CrudInventoryInlineActionController;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\CrudInventoryBuilder;
use Ucscode\UssElement\UssElement;

# This class establishes and creates features for the CrudInventory;

class CrudInventory extends AbstractCrudInventory_Level2
{    
    public function build(): UssElement
    {
        new CrudInventoryInlineActionController($this);
        parent::build();
        new CrudWidgetManager($this);
        new CrudInventoryBuilder($this);
        return $this->baseContainer;
    }

    public function setTableBackgroundWhite(bool $status = true): self
    {
        $wrappers = [
            'card' => $this->entitiesContainer,
            'card-body' => $this->domTable->getTableWrapperElement(),
        ];

        foreach($wrappers as $className => $element) {
            $method = $status ? "addAttributeValue" : "removeAttributeValue";
            $element->{$method}('class', $className);
        }

        return $this;
    }

    public function setTableBordered(bool $status = true): self
    {
        $method = $status ? "addAttributeValue" : "removeAttributeValue";
        $this->domTable->getTableElement()->{$method}('class', 'table-bordered');
        return $this;
    }
}