<?php

namespace Module\Dashboard\Bundle\Crud\Kernel\Interface;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Ucscode\UssElement\UssElement;

interface CrudKernelInterface
{
    public function build(): UssElement;
    public function setPrimaryOffset(string $offset): self;
    public function getPrimaryOffset(): string;
    public function getEntitiesContainer(): UssElement;
    public function getBaseContainer(): UssElement;
    public function getWidgetsContainer(): UssElement;
    public function setWidget(string $name, CrudWidgetInterface $widgetInterface): self;
    public function getWidget(string $name): ?CrudWidgetInterface;
    public function hasWidget(string $name): bool;
    public function removeWidget(string $name): self;
    public function getWidgets(): array;
    public function disableWidgets(bool $status): self;
    public function isWidgetsDisabled(): bool;
    public function getChannel(): CrudEnum;
}