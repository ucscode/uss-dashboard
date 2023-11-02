<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\DOMTable\DOMTable;
use Ucscode\UssElement\UssElement;

final class CrudItemIterator implements DOMTableInterface
{
    private bool $isDropdown;

    public function __construct(
        private ?DOMTableInterface $fabricator,
        private CrudIndexManager $crudIndexManager,
        private Closure $checker,
        private DOMTable $domTable
    ) {
        $this->isDropdown = !$this->crudIndexManager->isDisplayItemActionsAsButton();
    }

    /**
     * @overrides
     * @method forEachItem
     */
    public function forEachItem(array $data): ?array
    {
        if($this->fabricator) {
            // process developer fabricator
            $data = $this->fabricator->forEachItem($data);
        }

        if($data) {
            // process system fabricator
            $data = $this->addCheckboxToItem($data);
            $data = $this->addActionsToItem($data);
            $data = $this->searchEachItem($data);
        }

        return $data;
    }

    /**
     * @method addCheckBox
     */
    protected function addCheckboxToItem(array $data): array
    {
        if($this->crudIndexManager->isBulkActionsHidden() === false) {
            $key = $this->crudIndexManager->getPrimaryKey();
            $value = $data[$key] ?? null;
            $checker = ($this->checker)($value, function ($input) {
                $input->setAttribute('data-ui-checkbox', 'single');
            });
            $data = ['__checkbox__' => $checker] + $data;
        };
        return $data;
    }

    /**
     * @method addActionsToItem
     */
    public function addActionsToItem(array $data): array
    {
        if($this->crudIndexManager->isItemActionsHidden() === false) {

            $nodelist = $this->createActionContainerElements();
            $actionsPerItem = $this->crudIndexManager->getItemActions();

            foreach($actionsPerItem as $crudActionInterface) {
                $crudAction = $crudActionInterface->forEachItem($data);
                $element = $this->createActionElement($crudAction);
                $this->appendElementToItem($element, $nodelist['contentBlock'], $crudAction);
            }

            $data['__actions__'] = $nodelist['container'];
        };
        return $data;
    }
    /**
     * @method createActionContainerElement
     */
    protected function createActionContainerElements(): array
    {
        $nodelist = [
            'container' => new UssElement(UssElement::NODE_DIV),
            'contentBlock' => null
        ];

        if($this->isDropdown) {

            $nodelist['container']->setAttribute('class', 'dropdown');

            $anchor = new UssElement(UssElement::NODE_A);
            $anchor->setAttribute('class', 'btn btn-outline-secondary btn-sm');
            $anchor->setAttribute('href', 'javascript:void(0)');
            $anchor->setAttribute('role', 'button');
            $anchor->setAttribute('data-bs-toggle', 'dropdown');
            $anchor->setAttribute('aria-expanded', 'false');
            $anchor->setContent('<i class="bi bi-list"></i>');

            $nodelist['contentBlock'] = new UssElement(UssElement::NODE_UL);
            $nodelist['contentBlock']->setAttribute('class', 'dropdown-menu');

            $nodelist['container']->appendChild($anchor);
            $nodelist['container']->appendChild($nodelist['contentBlock']);

        } else {

            $nodelist['contentBlock'] = $nodelist['container'];
        }

        return $nodelist;
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
            $element->setAttribute('href', $crudAction->getElementAttribute('href') ?? '#');
            $element->setContent($icon . $crudAction->getLabel());
        } else {
            $element = new UssElement(UssElement::NODE_BUTTON);
            $element->setAttribute('class', 'btn text-nowrap');
            $element->setAttribute('type', 'button');
            $element->setContent($icon . $crudAction->getLabel());
            $crudAction->removeElementAttribute('type');
        };

        return $element;
    }

    /**
     * @method appendToContainer
     */
    public function appendElementToItem(UssElement $element, UssElement $container, CrudAction $crudAction): void
    {
        $class = $crudAction->getElementAttribute('class');

        if($this->isDropdown) {
            $crudAction->setElementAttribute('class', 'dropdown-item ' . $class);
            $li = new UssElement(UssElement::NODE_LI);
            $li->appendChild($element);
            $container->appendChild($li);
        } else {
            if(empty($class)) {
                $crudAction->setElementAttribute('class', 'btn btn-outline-primary btn-sm text-nowrap');
            }
            $span = new UssElement(UssElement::NODE_SPAN);
            $span->setAttribute('class', 'mb-1 mx-1 d-inline-block');
            $span->appendChild($element);
            $container->appendChild($span);
        }

        foreach($crudAction->getElementAttributes() as $key => $value) {
            $element->setAttribute($key, $value);
        }
    }

    /**
     * @method searchItem
     */
    public function searchEachItem(array $data): ?array
    {
        $hasSearch = $this->crudIndexManager->getWidget('search');
        $keyword = strtolower(trim($_GET['search'] ?? ''));
        if($hasSearch && !empty($keyword)) {
            foreach($data as $key => $value) {
                if(is_scalar($value) && $key != 'id') {
                    $value = strip_tags($value);
                    $match = strpos(strtolower($value), $keyword) !== false;
                    if($match) {
                        return $data;
                    }
                }
            };
            return null;
        }
        return $data;
    }
}
