<?php

namespace Module\Dashboard\Bundle\Crud\Kernel\Interface;

use Ucscode\UssElement\UssElement;
use Uss\Component\Block\BlockTemplate;

interface CrudKernelInterface
{
    public function build(): UssElement;
    public function setPrimaryOffset(string $offset): self;
    public function getPrimaryOffset(): string;
    public function getEntitiesContainer(): UssElement;
    public function getBaseContainer(): UssElement;
    public function getWidgetsContainer(): UssElement;
    public function setWidget(string $name, BlockTemplate $blockTemplate): self;
    public function getWidget(string $name): ?BlockTemplate;
    public function removeWidget(string $name): self;
    public function getWidgets(): array;
    public function disableWidgets(bool $status): self;
    public function isWidgetsDisabled(): bool;
}