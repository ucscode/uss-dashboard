<?php

namespace Module\Dashboard\Bundle\Crud\Component;

use Module\Dashboard\Bundle\Crud\Compact\Interface\ActionInterface;
use Ucscode\UssElement\UssElement;

class Action implements ActionInterface
{
    protected UssElement $anchorNode;
    protected UssElement $buttonNode;
    protected UssElement $activeNode;

    public function __construct()
    {
        $this->anchorNode = $this->activeNode = new UssElement(UssElement::NODE_A);
        $this->buttonNode = new UssElement(UssElement::NODE_BUTTON);
    }

    public function setAsButtonNode(): self
    {
        $this->activeNode = $this->buttonNode;
        return $this;
    }

    public function setAsAnchorNode(): self
    {
        $this->activeNode = $this->anchorNode;
        return $this;
    }

    public function isButtonNode(): bool
    {
        return $this->activeNode === $this->buttonNode;
    }

    public function isAnchorNode(): bool
    {
        return $this->activeNode === $this->anchorNode;
    }

    public function setContent(string|UssElement $content): self
    {
        return $this->parseElements(function (UssElement $element) use ($content) {
            $content instanceof UssElement ?
                $element->appendChild($content) : 
                $element->setContent($content);
        });
    }

    public function getContent(): string|UssElement
    {
        return $this->activeNode->getContent();
    }

    public function setDisabled(bool $disabled): self
    {
        return $this->parseElements(fn (UssElement $element) => $element->setAttribute('disabled'));
    }

    public function isDisabled(): bool
    {
        return $this->activeNode->hasAttribute('disabled');
    }

    public function addClass(string $className): self
    {
        $classNames = array_map('trim', explode(" ", $className));
        return $this->parseElements(function(UssElement $element) use ($classNames) {
            foreach($classNames as $className) {
                $element->addAttributeValue('class', $className);
            }
        });
    }

    public function removeClass(string $className): self
    {
        $classNames = array_map('trim', explode(" ", $className));
        return $this->parseElements(function(UssElement $element) use ($classNames) {
            foreach($classNames as $className) {
                $element->removeAttributeValue('class', $className);
            }
        });
    }

    public function getElement(): UssElement
    {
        return $this->activeNode;
    }

    public function setAttribute(string $name, ?string $value, bool $append = false): self
    {
        if($value) {
            $this->parseElements(function(UssElement $element) use($name, $value, $append) {
                $append ?
                    $element->addAttributeValue($name, $value) :
                    $element->setAttribute($name, $value);
            });
        }
        return $this;
    }

    public function getAttribute(string $name): ?string
    {
        return $this->activeNode->getAttribute($name);
    }

    public function removeAttribute(string $name, ?string $value = null): self
    {
        return $this->parseElements(function(UssElement $element) use ($name, $value) {
            $value === null ?
                $element->removeAttribute($name) :
                $element->removeAttributeValue($name, $value);
        });
    }

    protected function parseElements(callable $func): self
    {
        foreach([$this->anchorNode, $this->buttonNode] as $element) {
            $func($element);
        }
        return $this;
    }
}