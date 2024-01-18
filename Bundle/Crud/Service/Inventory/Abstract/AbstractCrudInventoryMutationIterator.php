<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Ucscode\UssElement\UssElement;
use Module\Dashboard\Bundle\Crud\Component\Action;

abstract class AbstractCrudInventoryMutationIterator
{
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

    protected function createDropdownElements(): array
    {
        $element = new UssElement(UssElement::NODE_DIV);
        $button = new UssElement(UssElement::NODE_BUTTON);
        $ul = new UssElement(UssElement::NODE_UL);

        $element->setAttribute('class', 'dropdown');

        $button
            ->setAttribute('class', 'btn btn-secondary dropdown-toggle')
            ->setAttribute('type', 'button')
            ->setAttribute('data-bs-toggle', 'dropdown')
            ->setAttribute('aria-expanded', false)
            ->setContent("?");

        $ul->setAttribute('class', 'dropdown-menu');

        $element->appendChild($button);
        $element->appendChild($ul);

        return [$element, $ul];
    }

    protected function insertDropdownInlineAction(UssElement $dropdownListContainer, Action $action): void
    {
        $li = new UssElement(UssElement::NODE_LI);
        $action->removeClass('btn')->addClass('dropdown-item small');
        $li->appendChild($action->getElement());
        $dropdownListContainer->appendChild($li);
    }
}