<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

interface CrudEditInterface
{
    public const CRUD_NAME = 'edit';

    public function setReadOnly(bool $readonly): self;

    public function isReadOnly(): bool;

    public function setModifier(?CrudEditSubmitInterface $modifier): self;

    public function getModifier(): ?CrudEditSubmitInterface;

    public function setReadonlyModifier(?DOMTableInterface $modifier): self;

    public function getReadonlyModifier(): ?DOMTableInterface;

    public function createUI(): UssElement;

    public function setField(string $name, UssFormField $field): self;

    public function getField(string $name): ?UssFormField;

    public function getFields(): array;

    public function removeField(string $name): self;

    public function setItem(array $item): self;

    public function getItem(?string $key): array|string|null;

    public function setItemBy(string $key, string $value): self;

    public function setAction(string $name, CrudAction $action): self;

    public function getAction(string $name): ?CrudAction;

    public function removeAction(string $name): self;

    public function getActions(): array;

    public function setSubmitUrl(?string $url): self;

    public function getSubmitUrl(): ?string;

    public function createItemEntity(?array $item): int|bool;

    public function deleteItemEntity(?array $item): bool;

    public function updateItemEntity(?array $item): bool;

    public function lastItemEntityError(): ?string;

    public function getEditForm(): UssForm;
}
