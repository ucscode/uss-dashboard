<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Ucscode\UssElement\UssElement;

class TableCheckbox
{
    protected UssElement $checkboxContainer;
    protected UssElement $checkboxInput;
    protected string $delegate;

    public function __construct(protected ?array $item = null, protected ?CrudInventoryInterface $crudInventory = null)
    {
        $this->createCheckboxContainer();
        $this->createCheckboxInput();
        $this->createComposition();
    }

    public function getElement(): UssElement
    {
        return $this->checkboxContainer;
    }

    protected function createCheckboxContainer(): void
    {
        $this->checkboxContainer = new UssElement(UssElement::NODE_DIV);
        $this->checkboxContainer->setAttribute('class', 'form-check');
    }

    protected function createCheckboxInput(): void
    {
        $this->checkboxInput = new UssElement(UssElement::NODE_INPUT);
        $this->checkboxInput
            ->setAttribute("class", "form-check-input")
            ->setAttribute('type', 'checkbox')
        ;
    }

    protected function createComposition(): void
    {
        $this->item === null ? $this->delegate = 'multiple' : $this->createAdvanceDelegate();
        $this->checkboxInput->setAttribute('data-ui-checkbox', $this->delegate);
        $this->checkboxContainer->appendChild($this->checkboxInput);
    }

    protected function createAdvanceDelegate(): void
    {
        $formId = $this->crudInventory
            ?->getGlobalActionForm()
            ->getElement()
            ->getAttribute('id');
        
        $offset = $this->crudInventory->getPrimaryOffset();
        $value = $this->item[$offset] ?? null;
        $value instanceof UssElement ? $value = null : null;

        $this->delegate = 'single';
        $this->checkboxInput->setAttribute('form', $formId);
        $this->checkboxInput->setAttribute('name', 'entity[]');
        $this->checkboxInput->setAttribute('value', $value);
    }
}