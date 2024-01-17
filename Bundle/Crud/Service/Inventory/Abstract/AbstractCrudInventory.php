<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Compact\Abstract\AbstractCrudKernel;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Ucscode\DOMTable\DOMTable;
use Ucscode\SQuery\SQuery;

abstract class AbstractCrudInventory extends AbstractCrudKernel implements CrudInventoryInterface
{
    protected DOMTable $domTable;

    public function __construct(string $tableName)
    {
        parent::__construct($tableName);
        $this->configureInventory();
    }

    protected function configureInventory(): void
    {
        $this->domTable = new DOMTable($this->tableName);
        $squery = (new SQuery())
            ->select()
            ->from($this->tableName);
    }
}