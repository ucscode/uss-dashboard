<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Interface;

use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;

interface CrudInventoryInterface extends CrudInventoryActionInterface, CrudKernelInterface
{
    public function getSQuery(): SQuery;
    public function getDOMTable(): DOMTable;
    public function addEntityMutationIterator(string $name, ?DOMTableIteratorInterface $entityIterator): self;
    public function getEntityMutationIterator(string $name): ?DOMTableIteratorInterface;
    public function removeEntityMutationIterator(string $name): self;
    public function hasEntityMutationIterator(string $name): bool;
    public function getEntityMutationIterators(): array;
    public function setTableBackgroundWhite(bool $status): self;
    public function setTableBordered(bool $status): self;
    public function sortColumns(callable $sorter, bool $keySort = false): self;
    public function setColumns(array $columns): self;
    public function getColumns(): array;
    public function setColumn(string $key, ?string $displayText = null): self;
    public function removeColumn(string $key): self;
    public function setItemsPerPage(int $chunks): self;
    public function setCurrentPage(int $page): self;
    public function getPaginatorContainer(): UssElement;
}