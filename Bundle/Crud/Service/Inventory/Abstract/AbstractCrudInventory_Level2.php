<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Action\InlineDeleteAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Action\InlineEditAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Action\InlineViewAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\CrudInventoryBuilder;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\InventoryGlobalAction;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets\SearchWidget;
use Ucscode\DOMTable\DOMTable;
use Ucscode\SQuery\Condition;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;

abstract class AbstractCrudInventory_Level2 extends AbstractCrudInventory_Level3
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
        $this->globalActionForm = (new InventoryGlobalAction())->getForm();

        $this->domTable = new DOMTable($this->tableName);
        $this->domTable->setColumns($this->tableColumns);
        $this->domTable->setCurrentPage($_GET[CrudInventoryBuilder::PAGE_INDICATOR] ?? 1);
        $this->domTable->getTableElement()->setAttribute("data-ui-table", "inventory");
        $this->domTable->getTableElement()
            ->setAttribute(
                'data-form-id', 
                $this->globalActionForm->getElement()->getAttribute('id')
            );

        $this->sQuery = (new SQuery())->select()->from($this->tableName);
        if($condition) {
            $this->sQuery->where($condition);
        }
    }

    protected function createInventoryResources(): void
    {
        $this->setWidget("inventory:search", new SearchWidget());
        $this->setInlineAction('inventory:edit', new InlineEditAction());
        $this->setInlineAction('inventory:delete', new InlineDeleteAction());
        $this->setInlineAction('inventory:view', new InlineViewAction());
        $this->setGlobalAction('inventory:delete', $this->createGlobalDeleteAction());
    }

    protected function designateInventoryComponents(): void
    {
        $this->globalActionsContainer = $this->createElement(UssElement::NODE_DIV, 'actions-container my-1');
        $this->paginatorContainer = $this->createElement(UssElement::NODE_DIV, 'paginator-container my-2');
        $this->baseContainer->appendChild($this->paginatorContainer);
        $this->baseContainer->appendChild($this->globalActionsContainer);
        $this->domTable->getTableElement()->addAttributeValue('class', 'table-striped table-hover');
        $this->globalActionForm->export();
        $this->globalActionsContainer->appendChild($this->globalActionForm->getElement());
    }

    protected function createGlobalDeleteAction(): Action
    {
        $action = new Action();
        $action->setValue('delete');
        $action->setAttribute('data-ui-confirm', "You are about to delete {{items}} items");
        return $action;
    }
}