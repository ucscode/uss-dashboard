<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\SQuery\Condition;
use Ucscode\SQuery\SQuery;

abstract class AbstractCrudInventory extends AbstractCrudInventoryFactory implements CrudInventoryInterface
{
    protected array $inlineActions = [];
    protected DOMTable $domTable;
    protected ?DOMTableIteratorInterface $iteratorInterface;
    protected SQuery $sQuery;

    public function __construct(string $tableName, ?Condition $condition = null)
    {
        parent::__construct($tableName);
        $this->configureInventory($condition);
        $this->createInventoryResources();
        $this->designInventoryComponents();
    }

    protected function configureInventory(?Condition $condition): void
    {
        $this->domTable = new DOMTable($this->tableName);
        $this->domTable->setColumns($this->tableColumns);
        $this->sQuery = (new SQuery())->select()->from($this->tableName);
        $condition ? $this->sQuery->where($condition) : null;
    }

    protected function designInventoryComponents(): void
    {
        $this->domTable->getTableElement()->addAttributeValue('class', 'table-striped table-hover');
    }
}