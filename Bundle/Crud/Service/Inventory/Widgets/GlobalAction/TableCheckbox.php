<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets\GlobalAction;

use Ucscode\UssElement\UssElement;

class TableCheckbox
{
    public readonly UssElement $container;
    public readonly UssElement $input;

    public function __construct(protected ?string $value = null, protected ?string $formId = null)
    {
        $this->createCheckboxContainer();
        $this->createCheckboxInput();
        $this->container->appendChild($this->input);
    }

    protected function createCheckboxContainer(): void
    {
        $this->container = new UssElement(UssElement::NODE_DIV);
        $this->container->setAttribute('class', 'form-check');
    }

    protected function createCheckboxInput(): void
    {
        $this->input = new UssElement(UssElement::NODE_INPUT);
        $this->input
            ->setAttribute("class", "form-check-input")
            ->setAttribute('type', 'checkbox')
            ->setAttribute('data-ui-checkbox', 'multiple')
        ;
        if($this->formId !== null) {
            $this->input
                ->setAttribute('data-ui-checkbox', 'single')
                ->setAttribute('name', 'entity[]')
                ->setAttribute('form', $this->formId)
                ->setAttribute('value', $this->value)
            ;
        }
    }
}