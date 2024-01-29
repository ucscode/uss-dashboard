<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory;

use Module\Dashboard\Bundle\Crud\Component\CrudWidgetManager;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractCrudInventory_Level1;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\CrudInventoryActionControl;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\CrudInventoryBuilder;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;

class CrudInventory extends AbstractCrudInventory_Level1
{    
    public function build(): UssElement
    {
        new CrudInventoryActionControl($this);
        parent::build();
        new CrudWidgetManager($this);
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

    public function addEntityMutationIterator(string $name, ?DOMTableIteratorInterface $entityIterator): self
    {
        $this->entityMutationIterators[$name] = $entityIterator;
        return $this;
    }

    public function getEntityMutationIterator(string $name): ?DOMTableIteratorInterface
    {
        return $this->entityMutationIterators[$name] ?? null;
    }

    public function removeEntityMutationIterator(string $name): self
    {
        if($this->hasEntityMutationIterator($name)) {
            unset($this->entityMutationIterators[$name]);
        }
        return $this;
    }

    public function hasEntityMutationIterator(string $name): bool
    {
        return array_key_exists($name, $this->entityMutationIterators);
    }

    public function getEntityMutationIterators(): array
    {
        return $this->entityMutationIterators;
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
}