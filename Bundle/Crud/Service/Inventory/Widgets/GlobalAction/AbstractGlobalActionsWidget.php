<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets\GlobalAction;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Kernel\Interface\ActionInterface;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;
use Ucscode\UssForm\Resource\Interface\WidgetContextInterface;

abstract class AbstractGlobalActionsWidget extends AbstractGlobalActionsWidgetFoundation
{
    protected function configureAssociateTable(): void
    {
        if(!$this->crudInventory->isGlobalActionsDisabled()) {

            $domTable = $this->crudInventory->getDOMTable();

            $tableElement = $domTable->getTableElement();
            $tableElement->setAttribute('data-ui-table', 'inventory');
            $tableElement->setAttribute('data-form-id', $this->form->getElement()->getAttribute('id'));
            
            $domTable->setColumn(
                self::CHECKBOX_KEY,
                (new TableCheckbox())->container->getHTML(true)
            );
            
            $this->crudInventory->sortColumns(function($a, $b) {
                $checkboxKey = self::CHECKBOX_KEY;
                return ($a === $checkboxKey) ? -1 : (($b === $checkboxKey) ? 1 : 0);
            }, true);
        }
    }

    protected function createFormComponents(): void
    {
        $collection = $this->form->getCollection(Form::DEFAULT_COLLECTION);
        $field = new Field(Field::NODE_SELECT);
        $collection->addField('global-action', $field);
        
        $selectButton = new UssElement(UssElement::NODE_BUTTON);
        $selectButton->setAttribute('type', Field::TYPE_SUBMIT);
        $selectButton->setAttribute('class', 'btn btn-primary');
        $selectButton->setContent("Apply");

        $fieldContext = $field->getElementContext();
        $fieldContext->label->setDOMHidden(true);
        $fieldContext->suffix->setValue($selectButton);
        $fieldContext->gadget->container->addClass('input-group-sm');

        $widget = $fieldContext->gadget->widget;
        $widget
            ->setOption('', '-- select --')
            ->addClass('text-capitalize')
            ->setAttribute('data-ui-bulk-select')
            ->setAttribute('name', 'action')
        ;

        $globalActions = $this->crudInventory->getGlobalActions();
        array_walk($globalActions, fn (Action $action) => $this->addGlobalAction(
            $fieldContext->gadget->widget,
            $action
        ));
    }

    protected function applyInlineCheckbox(): void
    {
        $formId = $this->form->getElement()->getAttribute('id');

        $this->crudInventory->addEntityMutationIterator(

            self::CHECKBOX_KEY, 

            new class($this->crudInventory, self::CHECKBOX_KEY, $formId) implements DOMTableIteratorInterface 
            {
                public function __construct(
                    protected CrudInventoryInterface $crudInventory, 
                    protected string $checkboxKey,
                    protected ?string $formId = null
                ){}

                public function foreachItem(array $item): ?array
                {
                    $offset = $this->crudInventory->getPrimaryOffset();
                    $item[$offset] ??= '';
                    $value = $item[$offset] instanceof UssElement ? '' : $item[$offset];

                    $item[$this->checkboxKey] = (new TableCheckbox($value, $this->formId))->container;

                    return $item;
                }
            }

        );
    }

    protected function addGlobalAction(WidgetContextInterface $widget, Action $action): void
    {
        $value = $action->getValue() ?? $action->getContent();
        $content = $action->getContent() ?? $action->getValue();

        $widget->setOption($value, $content);
        $optionElement = $widget->getOptionElement($value);

        foreach($action->getElement()->getAttributes() as $offset => $attribute) {
            $offset !== 'value' ? $optionElement->setAttribute($offset, $attribute) : null;
        }
    }
}