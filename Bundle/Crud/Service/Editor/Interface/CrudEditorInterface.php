<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Interface;

use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;

interface CrudEditorInterface extends FormManagerInterface
{
    public function setEntity(array $entity): self;
    public function setEntityByOffset(string $offsetValue): bool;
    public function getEntity(): array;
    public function hasEntity(): bool;
    public function persistEntity(): bool;
    public function deleteEntity(): bool;
    public function isPersistable(): bool;
    public function isEntityInDatabase(): bool;
    public function setEntityValue(string $columnName, ?string $value): self;
    public function getEntityValue(string $columnName): ?string;
    public function removeEntityValue(string $columnName): self;
    public function moveFieldToCollection(string|Field $field, string|Collection $collection): bool;
    public function detachField(string|Field $field, bool $hide): self;
    public function isFieldDetached(string|Field $field): bool;
}