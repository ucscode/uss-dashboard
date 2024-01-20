<?php

namespace Module\Dashboard\Bundle\Crud\Kernel\Abstract;

use Ucscode\UssElement\UssElement;
use Uss\Component\Block\BlockTemplate;

abstract class AbstractCrudKernel extends AbstractCrudKernel_Level2
{
    public function setWidget(string $name, BlockTemplate $widget): self
    {
        $this->widgets[$name] = $widget;
        return $this;
    }

    public function getWidget(string $name): ?BlockTemplate
    {
        return $this->widgets[$name] ?? null;
    }

    public function removeWidget(string $name): self
    {
        $widget = $this->getWidget($name);
        if($widget) {
            unset($this->widgets[$name]);
        }
        return $this;
    }

    public function getWidgets(): array
    {
        return $this->widgets;
    }

    public function disableWidgets(bool $status = true): self
    {
        $this->widgetsDisabled = $status;
        $this->widgetsContainer->setInvisible($status);
        return $this;
    }

    public function isWidgetsDisabled(): bool
    {
        return $this->widgetsDisabled;
    }

    public function getBaseContainer(): UssElement
    {
        return $this->baseContainer;
    }

    public function getWidgetsContainer(): UssElement
    {
        return $this->widgetsContainer;
    }

    public function getEntitiesContainer(): UssElement
    {
        return $this->entitiesContainer;
    }

    public function setPrimaryOffset(string $offset): self
    {
        $this->primaryOffset = $offset;
        return $this;
    }

    public function getPrimaryOffset(): string
    {
        return $this->primaryOffset;
    }
}
