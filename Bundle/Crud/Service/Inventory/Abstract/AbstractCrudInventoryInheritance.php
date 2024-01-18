<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Compact\Abstract\AbstractCrudKernel;
use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\Element\InventoryGlobalAction;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;
use Ucscode\UssForm\Gadget\Context\WidgetContext;

abstract class AbstractCrudInventoryInheritance extends AbstractCrudInventory
{
    public function setGlobalAction(string $name, Action $action): self
    {
        parent::setGlobalAction($name, $action);
        [$value, $content] = $this->formulateAction($action);
        $this->obtainGlobalSelectField(true)?->setOption($value, $content);
        return $this;
    }

    public function removeGlobalAction(string $name): self
    {
        $action = $this->getGlobalAction($name);
        parent::removeGlobalAction($name);
        if(!empty($action)) {
            [$value, $content] = $this->formulateAction($action);
            $this->obtainGlobalSelectField(true)?->removeOption($value);
        }
        return $this;
    }

    protected function formulateAction(Action $action): array
    {
        $value = $action->getValue() ?? $action->getContent();
        $content = $action->getContent() ?? $action->getValue();
        return [$value, $content];
    }

    protected function obtainGlobalSelectField(bool $getWidget = false): null|Field|WidgetContext
    {
        $collection = $this->getGlobalActionForm()->getCollection(Form::DEFAULT_COLLECTION);
        $field = $collection->getField(InventoryGlobalAction::FIELD_NAME);
        return $getWidget ? $field?->getElementContext()->widget : $field;
    }
}