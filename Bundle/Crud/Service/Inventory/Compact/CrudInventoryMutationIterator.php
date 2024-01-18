<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractCrudInventoryMutationIterator;
use Module\Dashboard\Bundle\Crud\Service\Inventory\CrudInventory;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\UssElement\UssElement;

class CrudInventoryMutationIterator extends AbstractCrudInventoryMutationIterator implements DOMTableIteratorInterface
{
    public const ACTION_KEY = 'action:inline';

    protected ?DOMTableIteratorInterface $itemsMutationIterator;
    protected bool $inlineActionEnabled;

    public function __construct(protected CrudInventory $crudInventory)
    {
        $this->itemsMutationIterator = $crudInventory->getItemsMutationIterator();
        $this->inlineActionEnabled = $crudInventory->isInlineActionEnabled();
    }

    public function foreachItem(array $item): ?array
    {
        $this->itemsMutationIterator ? $item = $this->itemsMutationIterator->foreachItem($item) : null;
        if($item) {
            $item = $this->applySearchConcept($item);
            if($item && $this->inlineActionEnabled) {
                $item = $this->applyInlineActions($item);
            }
        }
        return $item;
    }

    protected function applySearchConcept(array $item): ?array
    {
        $search = strtolower(trim($_GET['search'] ?? ''));
        if(strlen($search)) {
            foreach($item as $value) {
                $value = strtolower(trim($this->extractDOMValue($value)));
                if(strpos($value, $search) !== false) {
                    return $item;
                };
            }
            return null;
        } 
        return $item;
    }

    protected function applyInlineActions(array $item): array
    {
        $inlineActions = $this->crudInventory->getInlineActions();
        if(!empty($inlineActions)) {
            $inlineActionContainer = (new UssElement(UssElement::NODE_DIV))->setAttribute('class', 'inline-action-container');
            if($this->crudInventory->isInlineActionAsDropdown()) {
                [$dropdownContainer, $dropdownListContainer] = $this->createDropdownElements();
                foreach($inlineActions as $inlineActionInterface) {
                    $action = $inlineActionInterface->foreachItem($item);
                    $this->insertDropdownInlineAction($dropdownListContainer, $action);
                }
                $inlineActionContainer->appendChild($dropdownContainer);
            } else {
                $plainContainer = (new UssElement(UssElement::NODE_DIV))->setAttribute('class', 'plain');
                foreach($inlineActions as $inlineActionInterface) {
                    $action = $inlineActionInterface->foreachItem($item);
                    $plainContainer->appendChild($action->getElement());
                }
                $inlineActionContainer->appendChild($plainContainer);
            }
            $item[self::ACTION_KEY] = $inlineActionContainer;
        }
        return $item;
    }
}