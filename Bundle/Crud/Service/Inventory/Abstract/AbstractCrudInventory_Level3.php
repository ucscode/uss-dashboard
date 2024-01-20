<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\InventoryGlobalAction;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;
use Ucscode\UssForm\Gadget\Context\WidgetContext;

abstract class AbstractCrudInventory_Level3 extends AbstractCrudInventoryFoundation
{
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