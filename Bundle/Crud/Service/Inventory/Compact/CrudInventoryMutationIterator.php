<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Module\Dashboard\Bundle\Crud\Service\Inventory\CrudInventory;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\UssElement\UssElement;

class CrudInventoryMutationIterator implements DOMTableIteratorInterface
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
        return $item;
    }

    protected function extractDOMValue(UssElement|string|null $context): ?string
    {
        $value = [];
        if($context instanceof UssElement) {
            $children = $context->getChildren();
            if(!empty($children)) {
                foreach($children as $node) {
                    $value[] = $this->extractDOMValue($node);
                }
            } else {
                $value[] = $context->getContent();
            }
        } else {
            $value[] = $context;
        }
        return implode(" ", $value);
    }
}