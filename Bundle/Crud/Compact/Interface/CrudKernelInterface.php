<?php

namespace Module\Dashboard\Bundle\Crud\Compact\Interface;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Ucscode\UssElement\UssElement;

interface CrudKernelInterface
{
    public function setWidget(string $name, UssElement $widget): self;
    public function getWidget(string $name): ?UssElement;
    public function removeWidget(string $name): self;
    public function getWidgets(): array;
    public function getWidgetsContainer(): UssElement;
    public function setAction(string $name, Action $action): self;
    public function getAction(string $name): ?Action;
    public function removeAction(string $name): self;
    public function getActions(): array;
    public function getActionsContainer(): UssElement;
    public function getEntitiesContainer(): UssElement;
    public function getBaseContainer(): UssElement;
}