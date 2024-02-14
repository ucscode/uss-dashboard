<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Interface;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Common\Entity;

interface CrudEditorInterface extends FormManagerInterface
{
    public function setEntityProperties(array $entityProperties): self;
    public function setEntityPropertiesByOffset(string $offsetValue): bool;
    public function getEntity(): Entity;
    public function hasEntityProperties(): bool;
    public function persistEntity(): bool;
    public function deleteEntity(): bool;
    public function isPersistable(): bool;
    public function isEntityInDatabase(): bool;
    public function getLastPersistenceType(): ?CrudEnum;
    public function getLastPersistenceId(): ?int;
    public function moveFieldToCollection(string|Field $field, string|Collection $collection): bool;
    public function detachField(string|Field $field, bool $hide): self;
    public function isFieldDetached(string|Field $field): bool;
}