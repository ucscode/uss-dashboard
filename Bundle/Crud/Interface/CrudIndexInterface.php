<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\UssElement\UssElement;

interface CrudIndexInterface
{
    public const PAGE_INDEX_KEY = 'page';

    public const CRUD_NAME = 'index';

    public function setModifier(?DOMTableInterface $modifier): self;

    public function getModifier(): ?DOMTableInterface;

    public function createUI(): UssElement;

    public function getTotalItems(): int;

    public function setItemsPerPage(int $index): self;

    public function getItemsPerPage(): int;

    public function setCurrentPage(int $page): self;

    public function getCurrentPage(): int;

    public function setTableColumns(array $columns): self;

    public function getTableColumns(): array;

    public function setTableColumn(string $column, ?string $display): self;

    public function removeTableColumn(string $column): self;

    public function setDisplayTableFooter(bool $status): self;

    public function getDisplayTableFooter(): bool;

    public function addBulkAction(string $name, CrudAction $action): self;

    public function removeBulkAction(string $name): self;

    public function getBulkActions(?string $name): CrudAction|array|null;

    public function setHideBulkActions(bool $status): self;

    public function handleBulkActions(CrudBulkActionsInterface $handler): void;

    public function isBulkActionsHidden(): bool;

    public function setDisplayItemActionsAsButton(bool $status): self;

    public function isDisplayItemActionsAsButton(): bool;

    public function addItemAction(string $name, CrudActionInterface $action): self;

    public function removeItemAction(string $name): self;

    public function getItemActions(?string $name): CrudActionInterface|array|null;

    public function setHideItemActions(bool $status): self;

    public function isItemActionsHidden(): bool;

    public function updateSQuery(callable $updater): void;

    public function setTableWhiteBackground(bool $status): self;

    public function isTableWhiteBackground(): bool;
}