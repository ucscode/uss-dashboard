<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Action\InlineDeleteAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Action\InlineEditAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Action\InlineViewAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\CrudInventoryBuilder;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\Element\InventoryGlobalAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets\SearchWidget;
use Ucscode\DOMTable\DOMTable;
use Ucscode\SQuery\Condition;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;

abstract class AbstractCrudInventory extends AbstractCrudInventory_Level2
{
    public function __construct(string $tableName, ?Condition $condition = null)
    {
        parent::__construct($tableName);
        $this->configureInventory($condition);
        $this->createInventoryResources();
        $this->designateInventoryComponents();
    }

    protected function configureInventory(?Condition $condition): void
    {
        $this->domTable = new DOMTable($this->tableName);
        $this->domTable->setColumns($this->tableColumns);
        $this->domTable->setCurrentPage($_GET[CrudInventoryBuilder::PAGE_INDICATOR] ?? 1);
        $this->domTable->getTableElement()->setAttribute("data-ui-table", "crud");
        $this->sQuery = (new SQuery())->select()->from($this->tableName);
        if($condition) {
            $this->sQuery->where($condition);
        }
        $this->globalActionForm = (new InventoryGlobalAction())->getForm();
    }

    protected function createInventoryResources(): void
    {
        $this->setWidget("inventory:search", (new SearchWidget)->getElement());
        $this->setInlineAction('inventory:edit', new InlineEditAction());
        $this->setInlineAction('inventory:delete', new InlineDeleteAction());
        $this->setInlineAction('inventory:view', new InlineViewAction());
        $this->setGlobalAction('inventory:delete', (new Action())->setValue('delete'));
    }

    protected function designateInventoryComponents(): void
    {
        $this->paginatorContainer = $this->createElement(UssElement::NODE_DIV, 'paginator-container my-2');
        $this->baseContainer->appendChild($this->paginatorContainer);
        $this->domTable->getTableElement()->addAttributeValue('class', 'table-striped table-hover');
        $this->globalActionForm->export();
        $this->actionsContainer->appendChild($this->globalActionForm->getElement());
    }
}