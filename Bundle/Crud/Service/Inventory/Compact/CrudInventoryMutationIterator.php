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
    protected array $inlineActions;
    protected bool $inlineActionEnabled;
    protected bool $isDropdown;

    public function __construct(protected CrudInventory $crudInventory)
    {
        $this->itemsMutationIterator = $crudInventory->getItemsMutationIterator();
        $this->inlineActions = $this->crudInventory->getInlineActions();
        $this->inlineActionEnabled = $crudInventory->isInlineActionEnabled() && !empty($this->inlineActions);
        $this->isDropdown = $this->crudInventory->isInlineActionAsDropdown();
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
        $inlineActionFrame = new UssElement(UssElement::NODE_DIV);
        $inlineActionFrame->setAttribute('class', 'inline-action-frame');
        $item[self::ACTION_KEY] = $inlineActionFrame;

        [$inlineActionContainer, $dropdownListContainer] = $this->isDropdown ? 
            $this->createDropdownElements() :
            $this->createTraditionalElements();

        foreach($this->inlineActions as $inlineActionInterface) {
            $action = $inlineActionInterface->foreachItem($item);
            $this->isDropdown ? 
                $this->insertDropdownInlineAction($dropdownListContainer, $action) :
                $this->insertButtonInlineAction($inlineActionContainer, $action);
        }

        $inlineActionFrame->appendChild($inlineActionContainer);
        return $item;
    }
}