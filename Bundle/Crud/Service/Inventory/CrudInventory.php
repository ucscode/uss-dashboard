<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractCrudInventory;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\CrudInventoryBuilder;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\InlineActionInterface;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Form\Form;

class CrudInventory extends AbstractCrudInventory  
{    
    public function build(): UssElement
    {
        $inventoryBuilder = new CrudInventoryBuilder($this);
        return $this->baseContainer;
    }

    public function setInlineAction(string $name, InlineActionInterface $action): self
    {
        $this->inlineActions[$name] = $action;
        return $this;
    }

    public function getInlineAction(string $name): ?InlineActionInterface
    {
        return $this->inlineActions[$name] ?? null;
    }

    public function removeInlineAction(string $name): self
    {
        $inlineAction = $this->getInlineAction($name);
        if($inlineAction) {
            unset($this->inlineActions[$name]);
        }
        return $this;
    }

    public function getInlineActions(): array
    {
        return $this->inlineActions;
    }

    public function disableInlineAction(bool $disable = true): self
    {
        $this->inlineActionDisabled = $disable;
        return $this;
    }

    public function isInlineActionDisabled(): bool
    {
        return $this->inlineActionDisabled;
    }

    public function getSQuery(): SQuery
    {
        return $this->sQuery;
    }

    public function getDOMTable(): DOMTable
    {
        return $this->domTable;
    }

    public function setColumns(array $columns): self
    {
        $this->domTable->setColumns($columns);
        return $this;
    }

    public function setColumn(string $key, ?string $displayText = null): self
    {
        $this->domTable->setColumn($key, $displayText);
        return $this;
    }

    public function getColumns(): array
    {
        return $this->domTable->getColumns();
    }

    public function removeColumn(string $key): self
    {
        $this->domTable->removeColumn($key);
        return $this;
    }

    public function setItemsMutationIterator(?DOMTableIteratorInterface $itemsMutationIterator): self
    {
        $this->itemsMutationIterator = $itemsMutationIterator;
        return $this;
    }

    public function getItemsMutationIterator(): ?DOMTableIteratorInterface
    {
        return $this->itemsMutationIterator;
    }

    public function sortColumns(callable $sorter, bool $keySort = false): self
    {
        $columns = $this->getColumns();
        $keySort ? uksort($columns, $sorter) : uasort($columns, $sorter);
        $this->setColumns($columns);
        return $this;
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

    public function getPaginatorContainer(): UssElement
    {
        return $this->paginatorContainer;
    }

    public function setInlineActionAsDropdown(bool $status = true): self
    {
        $this->inlineActionDropdownActive = $status;
        return $this;
    }

    public function isInlineActionAsDropdown(): bool
    {
        return $this->inlineActionDropdownActive;
    }

    public function setItemsPerPage(int $chunks): self
    {
        $this->domTable->setItemsPerPage($chunks);
        return $this;
    }

    public function setCurrentPage(int $page): self
    {
        $this->domTable->setCurrentPage($page);
        return $this;
    }

    public function getGlobalActionForm(): Form
    {
        return $this->globalActionForm;
    }
}