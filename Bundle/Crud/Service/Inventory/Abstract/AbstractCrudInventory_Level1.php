<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Action\InlineDeleteAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Action\InlineEditAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Action\InlineViewAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\CrudInventoryBuilder;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets\GlobalAction\GlobalActionsWidget;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets\SearchWidget;
use Ucscode\DOMTable\DOMTable;
use Ucscode\SQuery\Condition;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;

# This class initializes the CrudInventory Properties;

abstract class AbstractCrudInventory_Level1 extends AbstractCrudInventoryFoundation
{
    public function __construct(string $tableName, ?Condition $condition = null)
    {
        parent::__construct($tableName);
        $this->DOMTableFactory();
        $this->SQueryFactory($condition);
        $this->createInventoryResources();
        $this->designateInventoryComponents();
    }

    protected function DOMTableFactory(): void
    {
        $this->domTable = new DOMTable($this->tableName);
        $this->domTable->setColumns($this->tableColumnsLabelled);
        $this->domTable->setCurrentPage($_GET[CrudInventoryBuilder::PAGE_INDICATOR] ?? 1);
        $this->domTable->getTableElement()->setAttribute("data-ui-table", "inventory");
    }

    protected function SQueryFactory(?Condition $condition): void
    {
        $this->sQuery = (new SQuery())
            ->select()
            ->from($this->tableName)
            ->orderBy($this->primaryOffset, 'DESC');

        !$condition ?: $this->sQuery->where($condition);
    }

    protected function createInventoryResources(): void
    {
        $this->setWidget("inventory:search", new SearchWidget());
        $this->setWidget("inventory:global-action", new GlobalActionsWidget());
        $this->setInlineAction('inventory:edit', new InlineEditAction());
        $this->setInlineAction('inventory:delete', new InlineDeleteAction());
        // $this->setInlineAction('inventory:view', new InlineViewAction());
        $this->setGlobalAction('inventory:delete', $this->createGlobalDeleteAction());
    }

    protected function designateInventoryComponents(): void
    {
        $this->paginatorContainer = $this->createElement(UssElement::NODE_DIV, 'paginator-container my-2');
        $this->baseContainer->appendChild($this->paginatorContainer);
        $this->domTable->getTableElement()->addAttributeValue('class', 'table-striped table-hover');
    }

    protected function createGlobalDeleteAction(): Action
    {
        return (new Action())
            ->setValue('delete')
            ->setAttribute('data-ui-confirm', "You are about to delete {items} items")
        ;
    }
}