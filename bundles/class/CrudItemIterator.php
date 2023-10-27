<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\DOMTable\DOMTable;
use Ucscode\UssElement\UssElement;

class CrudItemIterator implements DOMTableInterface 
{
    private bool $isDropdown;

    public function __construct(
        private ?DOMTableInterface $fabricator,
        private CrudIndexManager $crudIndexManager,
        private Closure $checker,
        private DOMTable $dOMTable
    ) {
        $this->isDropdown = !$this->crudIndexManager->isDisplayItemActionsAsButton();
    }

    /**
     * @overrides
     * @method forEachItem
     */
    public function forEachItem(array $data): array
    {
        if($this->fabricator) {
            $data = $this->fabricator->forEachItem($data);
        }

        $data = $this->addCheckboxToItem($data);
        $data = $this->addActionsToItem($data);

        return $data;
    }

    /**
     * @method addCheckBox
     */
    protected function addCheckboxToItem(array $data): array
    {
        if($this->crudIndexManager->isBulkActionsHidden() === false) {
            $checker = ($this->checker)();
            $data = ['checkbox' => $checker->getHTML()] + $data;
        };
        return $data;
    }

    /**
     * @method addActionsToItem
     */
    public function addActionsToItem(array $data): array
    {
        if($this->crudIndexManager->isItemActionsHidden() === false) 
        {
            $container = $this->createActionContainerElement();
            $actionsPerItem = $this->crudIndexManager->getItemActions();

            foreach($actionsPerItem as $crudActionInterface) {
                $crudAction = $crudActionInterface->forEachItem($data);
                $element = $this->createActionElement($crudAction);
                $this->appendToContainer($container, $element);
            }

            $data['actions'] = $container->getHTML();
        };
        return $data;
    }
    /**
     * @method createActionContainerElement
     */
    protected function createActionContainerElement(): UssElement
    {
        $node = new UssElement(UssElement::NODE_DIV);

        if($this->isDropdown) {

            $node->setAttribute('class', 'dropdown');

            $anchor = new UssElement(UssElement::NODE_A);
            $anchor->setAttribute('class', 'btn btn-outline-secondary btn-sm');
            $anchor->setAttribute('href', 'javascript:void(0)');
            $anchor->setAttribute('role', 'button');
            $anchor->setAttribute('data-bs-toggle', 'dropdown');
            $anchor->setAttribute('aria-expanded', 'false');
            $anchor->setContent('<i class="bi bi-list"></i>');

            $ul = new UssElement(UssElement::NODE_UL);
            $ul->setAttribute('class', 'dropdown-menu');

            $node->appendChild($anchor);
            $node->appendChild($ul);

        } else {
            
        }

        return $node;
    }

    /**
     * @method createActionElement
     */
    public function createActionElement(CrudAction $crudAction): UssElement
    {
        $icon = $crudAction->getIcon() ?? '';
        if(!empty($icon)) {
            $icon = sprintf("<i class='%s me-1'></i>", $icon);
        }

        if($crudAction->getElementType() === CrudAction::TYPE_ANCHOR) {
            $element = new UssElement(UssElement::NODE_A);
            $element->setAttribute('class', '');
            $element->setAttribute('href', $crudAction->getValue() ?? '#');
            $element->setContent($icon . $crudAction->getLabel());
        } else {
            $element = new UssElement(UssElement::NODE_BUTTON);
            $element->setAttribute('class', 'btn text-nowrap');
            $element->setAttribute('value', $crudAction->getValue());
            $element->setContent($icon . $crudAction->getLabel());
        };

        foreach($crudAction->getElementAttributes() as $key => $value) {
            $element->setAttribute($key, $value);
        }

        if($this->isDropdown) {
            $parent = new UssElement(UssElement::NODE_LI);
            $parent->setAttribute('class', 'dropdown-item');
        } else {
            $parent = new UssElement(UssElement::NODE_SPAN);
            $parent->setAttribute('class', 'mb-1 mx-1 d-inline-block');
            $element->addAttributeValue('class', 'btn btn-primary btn-sm text-nowrap');
        }

        $parent->appendChild($element);

        return $parent;
    }

    /**
     * @method appendToContainer
     */
    public function appendToContainer(UssElement $container, UssElement $item): void
    {
        if(!$this->isDropdown) {
            $container->appendChild($item);
        } else {
            $container->find('ul.dropdown-menu', 0)->appendChild($item);
        }
    }
}