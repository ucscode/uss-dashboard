<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractCrudInventoryMutationIterator;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\UssElement\UssElement;

class CrudInventoryMutationIterator extends AbstractCrudInventoryMutationIterator implements DOMTableIteratorInterface
{
    public const ACTION_KEY = 'action:inline';

    protected array $inlineActions;
    protected bool $inlineActionEnabled;
    protected bool $globalActionEnabled;
    protected bool $isDropdown;
    protected array $item;

    public function __construct(protected CrudInventoryInterface $crudInventory)
    {
        $this->inlineActions = $this->crudInventory->getInlineActions();
        $this->inlineActionEnabled = !$crudInventory->isInlineActionDisabled() && !empty($this->inlineActions);
        $this->globalActionEnabled = !$crudInventory->isGlobalActionsDisabled();
        $this->isDropdown = $this->crudInventory->isInlineActionAsDropdown();
    }

    public function foreachItem(array $item): ?array
    {
        $this->item = $item;
        
        foreach($this->crudInventory->getEntityMutationIterators() as $iterator) {
            $this->item = $iterator->foreachItem($this->item) ?? [];
        }

        if(!empty($this->item)) {
            $this->item = $this->applySearchConcept($this->item);
            if($this->item) {
                $this->inlineActionEnabled ? $this->item = $this->applyInlineActions($this->item) : null;
            }
        }

        return $this->item;
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
        $inlineActionFrame = new UssElement(UssElement::NODE_DIV);
        $inlineActionFrame->setAttribute('class', 'inline-action-frame');
        $item[self::ACTION_KEY] = $inlineActionFrame;

        [$inlineActionContainer, $dropdownListContainer] = $this->isDropdown ? 
            $this->createDropdownElements() :
            $this->createTraditionalElements();

        foreach($this->inlineActions as $inlineActionInterface) {
            $action = $inlineActionInterface->foreachItem($item, $this->crudInventory);
            $this->isDropdown ? 
                $this->insertDropdownInlineAction($dropdownListContainer, $action) :
                $this->insertButtonInlineAction($inlineActionContainer, $action);
        }

        $inlineActionFrame->appendChild($inlineActionContainer);
        return $item;
    }
}