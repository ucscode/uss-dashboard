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

        $element->setAttribute('class', 'dropdown position-static inline-action-container');

        $button
            ->setAttribute('class', 'btn btn-sm btn-dropdown')
            ->setAttribute('type', 'button')
            ->setAttribute('data-bs-toggle', 'dropdown')
            ->setAttribute('aria-expanded', false)
            ->setContent("<span class='bi bi-list'></span>");

        $ul->setAttribute('class', 'dropdown-menu');

        $element->appendChild($button);
        $element->appendChild($ul);

        return [$element, $ul];
    }

    protected function createTraditionalElements(): array
    {
        $inlineActionContainer = new UssElement(UssElement::NODE_DIV);
        $inlineActionContainer->setAttribute('class', 'inline-action-container');
        return [$inlineActionContainer, null];
    }

    protected function insertDropdownInlineAction(UssElement $dropdownListContainer, Action $action): void
    {
        $disregardedClasses = array_filter(
            explode(" ", $action->getAttribute('class')),
            fn ($className) => preg_match("/^(?:btn|(?:btn\-[a-z]+))$/", trim($className))
        );

        $action
            ->removeClass(implode(" ", $disregardedClasses))
            ->addClass('dropdown-item small')
        ;

        $li = new UssElement(UssElement::NODE_LI);
        $li->appendChild($action->getElement());
        
        $dropdownListContainer->appendChild($li);
    }

    protected function insertButtonInlineAction(UssElement $inlineActionContainer, Action $action): void
    {
        $action->addClass("btn btn-sm");
        $span = new UssElement(UssElement::NODE_SPAN);
        $span->setAttribute('class', 'action-button d-inline-block m-1');
        $span->appendChild($action->getElement());
        $inlineActionContainer->appendChild($span);
    }
}