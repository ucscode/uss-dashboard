<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\InlineActionInterface;
use Module\Dashboard\Bundle\Crud\Component\Action;
use Ucscode\UssElement\UssElement;

abstract class AbstractCrudInventory_Level1 extends AbstractCrudInventory_Level2
{
    public function setInlineAction(string $name, InlineActionInterface $action): self
    {
        $this->inlineActions[$name] = $action;
        return $this;
    }

    public function getInlineAction(string $name): ?InlineActionInterface
    {
        return $this->inlineActions[$name] ?? null;
    }

    public function removeInlineAction(string $name): self
    {
        $inlineAction = $this->getInlineAction($name);
        if($inlineAction) {
            unset($this->inlineActions[$name]);
        }
        return $this;
    }

    public function getInlineActions(): array
    {
        return $this->inlineActions;
    }

    public function disableInlineAction(bool $disable = true): self
    {
        $this->inlineActionDisabled = $disable;
        return $this;
    }

    public function isInlineActionDisabled(): bool
    {
        return $this->inlineActionDisabled;
    }

    public function setInlineActionAsDropdown(bool $status = true): self
    {
        $this->inlineActionDropdownActive = $status;
        return $this;
    }

    public function isInlineActionAsDropdown(): bool
    {
        return $this->inlineActionDropdownActive;
    }

    public function setGlobalAction(string $name, Action $action): self
    {
        $this->globalActions[$name] = $action;
        [$value, $content] = $this->formulateAction($action);
        $widget = $this->obtainGlobalSelectField(true);
        if($widget) {
            $widget->setOption($value, $content);
            $this->polyfillAttributes($widget, $value, $action);
        }
        return $this;
    }

    public function removeGlobalAction(string $name): self
    {
        $action = $this->getGlobalAction($name);
        if(array_key_exists($name, $this->globalActions)) {
            unset($this->globalActions[$name]);
        }
        if(!empty($action)) {
            [$value, $content] = $this->formulateAction($action);
            $this->obtainGlobalSelectField(true)?->removeOption($value);
        }
        return $this;
    }

    public function getGlobalAction(string $name): ?Action
    {
        return $this->globalActions[$name] ?? null;
    }

    public function disableGlobalActions(bool $status = true): self
    {
        $this->globalActionsDisabled = $status;
        $this->globalActionsContainer->setInvisible($status);
        return $this;
    }

    public function isGlobalActionsDisabled(): bool
    {
        return $this->globalActionsDisabled;
    }

    public function getGlobalActions(): array
    {
        return $this->globalActions;
    }

    public function getGlobalActionsContainer(): UssElement
    {
        return $this->globalActionsContainer;
    }
}