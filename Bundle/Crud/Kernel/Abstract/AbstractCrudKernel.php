<?php

namespace Module\Dashboard\Bundle\Crud\Kernel\Abstract;

use Ucscode\UssElement\UssElement;
use Module\Dashboard\Bundle\Crud\Component\Action;

abstract class AbstractCrudKernel extends AbstractCrudKernel_Level2
{
    public function setWidget(string $name, UssElement $widget): self
    {
        $this->replaceElement($this->widgetsContainer, $widget, $this->getWidget($name));
        $this->widgets[$name] = $widget;
        return $this;
    }

    public function getWidget(string $name): ?UssElement
    {
        return $this->widgets[$name] ?? null;
    }

    public function removeWidget(string $name): self
    {
        $widget = $this->getWidget($name);
        if($widget) {
            unset($this->widgets[$name]);
            $widget->getParentElement()?->removeChild($widget);
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

    public function setGlobalAction(string $name, Action $action): self
    {
        $this->actions[$name] = $action;
        return $this;
    }

    public function getGlobalAction(string $name): ?Action
    {
        return $this->actions[$name] ?? null;
    }

    public function removeGlobalAction(string $name): self
    {
        if(array_key_exists($name, $this->actions)) {
            unset($this->actions[$name]);
        }
        return $this;
    }

    public function disableGlobalActions(bool $status = true): self
    {
        $this->actionsDisabled = $status;
        $this->actionsContainer->setInvisible($status);
        return $this;
    }

    public function isGlobalActionsDisabled(): bool
    {
        return $this->actionsDisabled;
    }

    public function getGlobalActions(): array
    {
        return $this->actions;
    }

    public function getBaseContainer(): UssElement
    {
        return $this->baseContainer;
    }

    public function getWidgetsContainer(): UssElement
    {
        return $this->widgetsContainer;
    }

    public function getGlobalActionsContainer(): UssElement
    {
        return $this->actionsContainer;
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
