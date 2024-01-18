<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\InventoryGlobalAction;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;
use Ucscode\UssForm\Gadget\Context\WidgetContext;

abstract class AbstractCrudInventory_Level2 extends AbstractCrudInventoryFoundation
{
    public function setGlobalAction(string $name, Action $action): self
    {
        parent::setGlobalAction($name, $action);
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

    protected function polyfillAttributes(WidgetContext $widget, ?string $optionValue, Action $action): void
    {
        $optionElement = $widget->getOptionElement($optionValue);
        if($optionElement) {
            foreach($action->getElement()->getAttributes() as $key => $value) {
                if(strtolower($key) != 'value') {
                    $optionElement->setAttribute($key, $value);
                }
            }
        }
    }
}