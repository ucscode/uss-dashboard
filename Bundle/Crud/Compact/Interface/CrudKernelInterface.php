<?php

namespace Module\Dashboard\Bundle\Crud\Compact\Interface;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Ucscode\UssElement\UssElement;

interface CrudKernelInterface
{
    public function setPrimaryOffset(string $offset): self;
    public function getPrimaryOffset(): string;
    public function setWidget(string $name, UssElement $widget): self;
    public function getWidget(string $name): ?UssElement;
    public function removeWidget(string $name): self;
    public function getWidgets(): array;
    public function getWidgetsContainer(): UssElement;
    public function disableWidgets(bool $status): self;
    public function isWidgetsDisabled(): bool;
    public function setGlobalAction(string $name, Action $action): self;
    public function getGlobalAction(string $name): ?Action;
    public function removeGlobalAction(string $name): self;
    public function disableGlobalActions(bool $status): self;
    public function isGlobalActionsDisabled(): bool;
    public function getGlobalActions(): array;
    public function getGlobalActionsContainer(): UssElement;
    public function getEntitiesContainer(): UssElement;
    public function getBaseContainer(): UssElement;
}