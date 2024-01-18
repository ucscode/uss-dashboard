<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets;

use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Field\Foundation\ElementContext;
use Ucscode\UssForm\Form\Form;

class SearchWidget
{
    protected UssElement $element;
    protected Form $form;
    protected Field $field;
    protected UssElement $submitButton;
    protected Collection $collection;

    public function __construct()
    {
        $this->element = new UssElement(UssElement::NODE_DIV);
        $this->element->setAttribute('class', 'col-md-6');
        $this->prepareResources();
        $this->buildComponents($this->field->getElementContext());
        $this->alignItems();
    }

    public function getElement(): UssElement
    {
        return $this->element;
    }

    protected function prepareResources(): void
    {
        $this->form = new Form();
        $this->field = new Field(Field::NODE_INPUT, Field::TYPE_SEARCH);
        $this->collection = $this->form->getCollection(Form::DEFAULT_COLLECTION);
        $this->submitButton = new UssElement(UssElement::NODE_BUTTON);
        $this->form->getAttribute()->setMethod('get');
        $this->collection->addField("search", $this->field);
    }

    protected function buildComponents(ElementContext $fieldContext): void
    {
        $this->submitButton
            ->setAttribute('class', 'btn btn-primary')
            ->setContent("<i class='bi bi-search me-1'></i> Search");

        $fieldContext->label->setDOMHidden(true);
        $fieldContext->suffix->setValue($this->submitButton);
        $fieldContext->container->addClass('input-group-sm');

        $fieldContext->widget
            ->setRequired(false)
            ->addClass('form-control-sm')
            ->setAttribute('placeholder', 'Type here...')
            ->setValue($_GET['search'] ?? null)
        ;
    }

    protected function alignItems(): void
    {
        $this->form->export();
        $this->element->appendChild($this->form->getElement());
    }
}