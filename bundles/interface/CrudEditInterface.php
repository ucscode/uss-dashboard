<?php

use Ucscode\UssElement\UssElement;

interface CrudEditInterface
{
    public function createUI(?CrudEditSubmitInterface $submitInterface): UssElement;

    public function setField(string $name, CrudField $field): self;

    public function getField(string $name): ?CrudField;

    public function getFields(): array;

    public function removeField(string $name): self;

    public function setItem(array $item): self;

    public function getItem(?string $key): array|string|null;

    public function setAction(string $name, CrudAction $action): self;

    public function getAction(string $name): ?CrudAction;

    public function removeAction(string $name): self;

    public function getActions(): array;

    public function setWidget(string $name, UssElement $widget): self;

    public function getWidget(string $name): ?UssElement;

    public function getWidgets(): array;

    public function removeWidget(string $name): self;

    public function setPrimaryKey(string $key): self;

    public function getPrimaryKey(): string;

    public function setSubmitUrl(?string $url): self;

    public function getSubmitUrl(): ?string;
}
