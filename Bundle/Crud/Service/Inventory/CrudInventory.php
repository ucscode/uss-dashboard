<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractCrudInventory_Level1;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\CrudInventoryBuilder;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\CrudInventoryWidgetManager;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Form\Form;

class CrudInventory extends AbstractCrudInventory_Level1
{    
    public function build(): UssElement
    {
        new CrudInventoryWidgetManager($this);
        new CrudInventoryBuilder($this);
        return $this->baseContainer;
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