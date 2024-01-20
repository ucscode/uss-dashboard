<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Compact;

use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;
use Ucscode\UssForm\Gadget\Context\WidgetContext;
use Ucscode\UssForm\Gadget\Gadget;

class FieldPedigree
{
    public function __construct(
        public readonly WidgetContext $widget,
        public readonly Gadget $gadget,
        public readonly string $fieldName,
        public readonly Field $field,
        public readonly string $collectionName,
        public readonly Collection $collection,
        public readonly Form $form
    ){}
}