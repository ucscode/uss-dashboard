<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact\Element;

use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;

class InventoryGlobalAction
{
    public const FORM_ID = 'bucket-list';
    public const FIELD_NAME = 'globalAction';

    protected Form $form;
    protected Collection $collection;

    public function __construct()
    {
        $this->form = new Form();
        $this->collection = $this->form->getCollection(Form::DEFAULT_COLLECTION);
        $this->collection->addField(self::FIELD_NAME, $this->createSelectField());
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    protected function createSelectField(): Field
    {
        $field = new Field(Field::NODE_SELECT);
        $context = $field->getElementContext();
        $context->label->setDOMHidden(true);
        $context->suffix->setValue($this->submitButton());
        $context->container->addClass('input-group-sm');
        $context->frame
            ->removeClass('col-12')
            ->addClass('col-md-6 ms-auto')
        ;
        $context->widget
            ->setOption('', '-- select --')
            ->setAttribute('name', 'action')
            ->addClass('text-capitalize')
        ;
        return $field;
    }

    protected function submitButton(): UssElement
    {
        $button = new UssElement(UssElement::NODE_BUTTON);
        $button->setAttribute('class', 'btn btn-primary');
        $button->setContent('Apply');
        return $button;
    }
}