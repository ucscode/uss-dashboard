<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractCrudInventory;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\InlineActionInterface;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Uss\Component\Kernel\Uss;

class CrudInventory extends AbstractCrudInventory
{    
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

    public function getSQuery(): SQuery
    {
        return $this->sQuery;
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

    public function mutateItems(DOMTableIteratorInterface $iteratorInterface): self
    {
        $this->iteratorInterface = $iteratorInterface;
        return $this;
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

    public function build(): UssElement
    {
        $result = Uss::instance()->mysqli->query($this->sQuery->build());
        $this->domTable->setData($result, $this->iteratorInterface);
        $element = $this->domTable->build();
        $this->entitiesContainer->appendChild($element);
        return $this->baseContainer;
    }
}