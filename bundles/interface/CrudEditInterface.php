<?php

use Ucscode\UssElement\UssElement;

interface CrudEditInterface
{
    public function createUI(?CrudEditSubmitCustomInterface $submitInterface): UssElement;

    public function setField(string $name, CrudField $field): self;

    public function getField(string $name): ?CrudField;

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
}
