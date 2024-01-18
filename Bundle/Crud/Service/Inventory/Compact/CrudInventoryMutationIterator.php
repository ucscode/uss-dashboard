<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractCrudInventoryMutationIterator;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\TableCheckbox;
use Module\Dashboard\Bundle\Crud\Service\Inventory\CrudInventory;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\UssElement\UssElement;

class CrudInventoryMutationIterator extends AbstractCrudInventoryMutationIterator implements DOMTableIteratorInterface
{
    public const CHECKBOX_KEY = 'checkbox:inline';
    public const ACTION_KEY = 'action:inline';

    protected ?DOMTableIteratorInterface $itemsMutationIterator;
    protected array $inlineActions;
    protected bool $inlineActionEnabled;
    protected bool $globalActionEnabled;
    protected bool $isDropdown;

    public function __construct(protected CrudInventory $crudInventory)
    {
        $this->itemsMutationIterator = $crudInventory->getItemsMutationIterator();
        $this->inlineActions = $this->crudInventory->getInlineActions();
        $this->inlineActionEnabled = !$crudInventory->isInlineActionDisabled() && !empty($this->inlineActions);
        $this->globalActionEnabled = !$crudInventory->isGlobalActionsDisabled();
        $this->isDropdown = $this->crudInventory->isInlineActionAsDropdown();
    }

    public function foreachItem(array $item): ?array
    {
        $item = $this->itemsMutationIterator ? $this->itemsMutationIterator->foreachItem($item) : $item;
        if($item) {
            $item = $this->applySearchConcept($item);
            if($item) {
                $this->inlineActionEnabled ? $item = $this->applyInlineActions($item) : null;
                $this->globalActionEnabled ? $item = $this->applyInlineCheckbox($item) : null;
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

    protected function applyInlineCheckbox(array $item): array
    {
        $formId = $this->crudInventory
            ->getGlobalActionForm()
            ->getElement()
            ->getAttribute('id');

        $item[self::CHECKBOX_KEY] = (new TableCheckbox($item, $this->crudInventory))->getElement();

        return $item;
    }
}