<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\Element;

use Ucscode\UssElement\UssElement;

class TableCheckbox
{
    protected UssElement $checkboxContainer;
    protected UssElement $checkboxInput;
    protected string $delegate;

    public function __construct(protected ?array $item = null)
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
        $this->delegate = 'single';
        $this->checkboxInput->setAttribute('form', InventoryGlobalAction::FORM_ID);
        $this->checkboxInput->setAttribute('name', 'entity[]');
        $this->checkboxInput->setAttribute('value', 'wait');
    }
}