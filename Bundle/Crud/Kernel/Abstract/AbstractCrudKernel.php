<?php

namespace Module\Dashboard\Bundle\Crud\Kernel\Abstract;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudWidgetInterface;
use Ucscode\UssElement\UssElement;

abstract class AbstractCrudKernel extends AbstractCrudKernel_Level2
{
    public function build(): UssElement
    {
        if(empty($this->getWidgets())) {
            $this->disableWidgets(true);
        }
        return $this->baseContainer;
    }

    public function setWidget(string $name, CrudWidgetInterface $widget): self
    {
        $this->widgets[$name] = $widget;
        return $this;
    }

    public function getWidget(string $name): ?CrudWidgetInterface
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

    public function hasWidget(string $name): bool
    {
        return !!$this->getWidget($name);
    }

    public function getWidgets(): array
    {
        return $this->widgets;
    }

    public function disableWidgets(bool $status = true): self
    {
        $this->widgetsDisabled = $status;
        $this->widgetsContainer->setInvisible($status);
        $this->dividerElement->setInvisible($status);
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

    public function getChannel(): CrudEnum
    {
        return match($_GET['channel'] ?? null) {
            CrudEnum::CREATE->value => CrudEnum::CREATE,
            CrudEnum::DELETE->value => CrudEnum::DELETE,
            CrudEnum::UPDATE->value => CrudEnum::UPDATE,
            default => CrudEnum::READ
        };
    }
}
