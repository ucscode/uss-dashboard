<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Interface;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;

interface CrudEditorFormInterface
{
    public function enablePersistence(bool $status): self;
    public function isPersistenceEnabled(): bool;
    public function isPersisted(): bool;
    public function getPersistenceLastInsertId(): int|string|null;
    public function getPersistenceError(): ?string;
    public function getPersistenceType(): ?CrudEnum;
}