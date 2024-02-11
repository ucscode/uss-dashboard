<?php

namespace Module\Dashboard\Bundle\Crud\Kernel\Abstract;

use Ucscode\UssElement\UssElement;

abstract class AbstractCrudKernel_Level1 extends AbstractCrudKernelFoundation
{
    public function __construct($tableName)
    {
        parent::__construct($tableName);
        $this->createGraphicalResource();
    }

    protected function createGraphicalResource(): void
    {
        $this->baseContainer = $this->createElement(UssElement::NODE_DIV, 'base-container');
        $this->widgetsContainer = $this->createElement(UssElement::NODE_DIV, 'widgets-container row my-1');
        $this->dividerElement = $this->createElement(UssElement::NODE_DIV, 'widget-entity-boundary border-top');
        $this->entitiesContainer = $this->createElement(UssElement::NODE_DIV, 'entities-container my-1');

        $this->baseContainer->appendChild($this->widgetsContainer);
        $this->baseContainer->appendChild($this->dividerElement);
        $this->baseContainer->appendChild($this->entitiesContainer);
    }

    protected function createElement(string $nodeName, ?string $className = null, string|UssElement|null $content = null): UssElement
    {
        $element = new UssElement($nodeName);
        $className ? $element->setAttribute('class', $className) : null;
        !$content ?: ($content instanceof UssElement ? $element->appendChild($element) : $element->setContent($content));
        return $element;
    }
}