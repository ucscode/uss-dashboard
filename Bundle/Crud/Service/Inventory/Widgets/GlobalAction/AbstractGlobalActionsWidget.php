<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets\GlobalAction;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\TableCheckbox;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;

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
                (new TableCheckbox())->getElement()->getHTML(true)
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
    }

    protected function applyInlineCheckbox(): void
    {
        $this->crudInventory->addEntityMutationIterator(

            self::CHECKBOX_KEY, 

            new class($this->crudInventory, self::CHECKBOX_KEY) implements DOMTableIteratorInterface 
            {
                public function __construct(
                    protected CrudInventoryInterface $crudInventory, 
                    protected string $checkboxKey
                ){}

                public function foreachItem(array $item): ?array
                {
                    $checkbox = new TableCheckbox($item, $this->crudInventory);
                    $item[$this->checkboxKey] = $checkbox->getElement();
                    return $item;
                }
            }

        );
    }
}