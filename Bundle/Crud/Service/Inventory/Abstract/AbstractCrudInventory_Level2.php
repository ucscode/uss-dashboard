<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\InlineActionInterface;
use Module\Dashboard\Bundle\Crud\Component\Action;
use Ucscode\SQuery\SQuery;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\UssElement\UssElement;

# This is a repository to manage the CrudInventory Properties;

abstract class AbstractCrudInventory_Level2 extends AbstractCrudInventory_Level1
{
    public function getSQuery(): SQuery
    {
        return $this->sQuery;
    }

    public function getDOMTable(): DOMTable
    {
        return $this->domTable;
    }

    # Inline Action Components;

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

    public function setInlineActionAsDropdown(bool $status = true): self
    {
        $this->inlineActionDropdownActive = $status;
        return $this;
    }

    public function isInlineActionAsDropdown(): bool
    {
        return $this->inlineActionDropdownActive;
    }

    # Global Action Components;

    public function setGlobalAction(string $name, Action $action): self
    {
        $this->globalActions[$name] = $action;
        return $this;
    }

    public function removeGlobalAction(string $name): self
    {
        if(array_key_exists($name, $this->globalActions)) {
            unset($this->globalActions[$name]);
        }
        return $this;
    }

    public function getGlobalAction(string $name): ?Action
    {
        return $this->globalActions[$name] ?? null;
    }

    public function disableGlobalActions(bool $status = true): self
    {
        $this->globalActionsDisabled = $status;
        return $this;
    }

    public function isGlobalActionsDisabled(): bool
    {
        return $this->globalActionsDisabled;
    }

    public function getGlobalActions(): array
    {
        return $this->globalActions;
    }

    # Entity Mutation Components;

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

    # DOMTable Components;

    public function setColumn(string $key, ?string $displayText = null): self
    {
        $this->domTable->setColumn($key, $displayText);
        return $this;
    }

    public function setColumns(array $columns): self
    {
        $this->domTable->setColumns($columns);
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

    public function sortColumns(callable $sorter, bool $keySort = false): self
    {
        $columns = $this->getColumns();
        $keySort ? uksort($columns, $sorter) : uasort($columns, $sorter);
        $this->setColumns($columns);
        return $this;
    }

    # UssElement Components;

    public function getPaginatorContainer(): UssElement
    {
        return $this->paginatorContainer;
    }
}