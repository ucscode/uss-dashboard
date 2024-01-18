<?php

namespace Module\Dashboard\Bundle\Crud\Compact\Abstract;

use Module\Dashboard\Bundle\Crud\Compact\Interface\CrudKernelInterface;
use Ucscode\UssElement\UssElement;
use Module\Dashboard\Bundle\Crud\Component\Action;

abstract class AbstractCrudKernel extends AbstractCrudComposition
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
        return $this;
    }

    public function isWidgetsDisabled(): bool
    {
        return $this->widgetsDisabled;
    }

    public function setAction(string $name, Action $action): self
    {
        $this->actions[$name] = $action;
        return $this;
    }

    public function getAction(string $name): Action
    {
        return $this->actions[$name] ?? null;
    }

    public function removeAction(string $name): self
    {
        if(array_key_exists($name, $this->actions)) {
            unset($this->actions[$name]);
        }
        return $this;
    }

    public function disableActions(bool $status = true): self
    {
        $this->actionsDisabled = $status;
        return $this;
    }

    public function isActionsDisabled(): bool
    {
        return $this->actionsDisabled;
    }

    public function getActions(): array
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

    public function getActionsContainer(): UssElement
    {
        return $this->actionsContainer;
    }

    public function getEntitiesContainer(): UssElement
    {
        return $this->entitiesContainer;
    }
}
