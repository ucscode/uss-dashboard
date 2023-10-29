<?php

use Ucscode\UssElement\UssElement;

interface CrudRelativeInterface
{
    public function rewriteCurrentPath(array $query): void;

    public function setPrimaryKey(string $key): self;

    public function getPrimaryKey(): string;

    public function setWidget(string $name, UssElement $widget): self;

    public function getWidget(string $name): ?UssElement;

    public function getWidgets(): array;

    public function removeWidget(string $name): self;

    public function setHideWidgets(bool $status): self;

    public function isWidgetsHidden(): bool;
}
