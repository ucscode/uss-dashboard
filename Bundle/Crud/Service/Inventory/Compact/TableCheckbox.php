<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Ucscode\UssElement\UssElement;

class TableCheckbox
{
    protected UssElement $checkboxContainer;
    protected UssElement $checkboxInput;

    public function __construct(protected string $delegate = 'single')
    {
        $this->createCheckboxContainer();
        $this->createCheckboxInput();
        $this->checkboxContainer->appendChild($this->checkboxInput);
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
            ->setAttribute('data-ui-checkbox', $this->delegate)
        ;
    }
}