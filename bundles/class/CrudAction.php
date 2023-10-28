<?php

class CrudAction
{
    public const TYPE_BUTTON = 'button';
    public const TYPE_ANCHOR = 'anchor';

    protected ?string $label = null;
    protected ?string $icon = null;
    protected ?string $elementType = self::TYPE_BUTTON;
    protected array $elementAttributes = [];

    /**
     * @method setLabel
     */
    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @method getLabel
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @method setIcon
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @method getIcon
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @method setRenderAs
     */
    public function setElementType(string $elementType): self
    {
        $types = [self::TYPE_BUTTON, self::TYPE_ANCHOR];
        if(!in_array($elementType, $types)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s: argument must be one of %s',
                    __METHOD__,
                    implode(", ", $types)
                )
            );
        }
        $this->elementType = $elementType;
        return $this;
    }

    /**
     * @method getRenderAs
     */
    public function getElementType(): string
    {
        return $this->elementType;
    }

    /**
     * @method setElementAttribute
     */
    public function setElementAttribute(string $key, ?string $value): self
    {
        $this->elementAttributes[$key] = $value;
        return $this;
    }

    /**
     * @method getElementAttribute
     */
    public function getElementAttribute(string $key): ?string
    {
        return $this->elementAttributes[$key] ?? null;
    }

    /**
     * @method removeElementAttribute
     */
    public function removeElementAttribute(string $key): self
    {
        if(array_key_exists($key, $this->elementAttributes)) {
            unset($this->elementAttributes[$key]);
        }
        return $this;
    }

    /**
     * @method getElementAttributes
     */
    public function getElementAttributes(): array
    {
        return $this->elementAttributes;
    }
}
