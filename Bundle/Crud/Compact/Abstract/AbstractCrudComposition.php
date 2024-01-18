<?php

namespace Module\Dashboard\Bundle\Crud\Compact\Abstract;

use Module\Dashboard\Bundle\Crud\Compact\Interface\CrudKernelInterface;
use Ucscode\UssElement\UssElement;
use Uss\Component\Kernel\Uss;

abstract class AbstractCrudComposition implements CrudKernelInterface
{
    protected UssElement $baseContainer;
    protected UssElement $widgetsContainer;
    protected UssElement $actionsContainer;
    protected UssElement $entitiesContainer;
    protected array $widgets = [];
    protected array $actions = [];
    protected array $tableColumns;

    public function __construct(public readonly string $tableName) 
    {
        $uss = Uss::instance();
        $this->tableColumns = array_map(
            fn ($value) => ucwords(str_replace("_", " ", $value)),
            $uss->getTableColumns($this->tableName)
        );
        $this->createGraphicalResource();
        $this->orientGraphicalResource();
    }

    protected function createGraphicalResource(): void
    {
        $this->baseContainer = $this->createElement(UssElement::NODE_DIV, 'base-container');
        $this->widgetsContainer = $this->createElement(UssElement::NODE_DIV, 'widgets-container row');
        $this->actionsContainer = $this->createElement(UssElement::NODE_DIV, 'actions-container');
        $this->entitiesContainer = $this->createElement(UssElement::NODE_DIV, 'entities-container');
    }

    protected function orientGraphicalResource(): void
    {
        $this->baseContainer->appendChild($this->widgetsContainer);
        $this->baseContainer->appendChild($this->actionsContainer);
        $this->baseContainer->appendChild($this->entitiesContainer);
    }

    protected function createElement(string $nodeName, ?string $className = null, string|UssElement|null $content = null): UssElement
    {
        $element = new UssElement($nodeName);
        $className ? $element->setAttribute('class', $className) : null;
        !$content ? null :
            (
                $content instanceof UssElement ? 
                $element->appendChild($element) : 
                $element->setContent($content)
            );
        return $element;
    }

    protected function replaceElement(UssElement $parent, UssElement $node, ?UssElement $reference): void
    {
        if($reference && $reference->getParentElement() === $parent) {
            $parent->replaceChild($node, $reference);
            return;
        }
        $parent->appendChild($node);
    }
}