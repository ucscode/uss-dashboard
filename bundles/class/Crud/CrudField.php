<?php

use Ucscode\UssForm\UssForm;

class CrudField
{
    public const TYPE_INPUT = UssForm::NODE_INPUT;
    public const TYPE_CHECKBOX = UssForm::TYPE_CHECKBOX;
    public const TYPE_NUMBER = UssForm::TYPE_NUMBER;
    public const TYPE_RADIO = UssForm::TYPE_RADIO;
    public const TYPE_DATE = UssForm::TYPE_DATE;
    public const TYPE_PASSWORD = UssForm::TYPE_PASSWORD;
    public const TYPE_FILE = UssForm::TYPE_FILE;
    public const TYPE_HIDDEN = UssForm::TYPE_HIDDEN;
    public const TYPE_BOOLEAN = 'BOOLEAN';
    public const TYPE_COLOR = UssForm::TYPE_COLOR;
    public const TYPE_EMAIL = UssForm::TYPE_EMAIL;
    public const TYPE_SELECT = UssForm::NODE_SELECT;
    public const TYPE_TEXTAREA = UssForm::NODE_TEXTAREA;
    public const TYPE_EDITOR = 'EDITOR';

    protected ?string $label = null;
    protected string $type = self::TYPE_INPUT;
    protected array $attributes = [];
    protected ?string $columnClass = null;
    protected ?string $class = null;
    protected bool $required = true;
    protected array $selectOptions = [];
    protected ?string $icon = null;
    protected bool $iconPositionRight = false;
    protected bool $readonly = false;
    protected bool $disabled = false;
    protected ?string $value = null;
    protected ?string $error = null;
    protected bool $lineBreak = false;

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
     * @method setType
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @method getType
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @method setAttributes
     */
    public function setAttribute(string $key, string $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @method getAttributes
     */
    public function getAttribute(string $key): ?string
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @method getAttributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @method setColumnClass
     */
    public function setColumnClass(string $columnClass): self
    {
        $this->columnClass = $columnClass;
        return $this;
    }

    /**
     * @method getColumnClass
     */
    public function getColumnClass(): ?string
    {
        $this->setDefaultLook();
        return $this->columnClass;
    }

    /**
     * @method setClass
     */
    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @method getClass
     */
    public function getClass(): ?string
    {
        $this->setDefaultLook();
        return $this->class;
    }

    /**
     * @method setRequired
     */
    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @method isRequired
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @method setSelectOptions
     */
    public function setSelectOptions(array $selectOptions): self
    {
        $this->selectOptions = $selectOptions;
        return $this;
    }

    /**
     * @method setSelectOption
     */
    public function setSelectOption(string $key, string $display): self
    {
        $this->selectOptions[$key] = $display;
        return $this;
    }

    /**
     * @method getSelectOptions
     */
    public function getSelectOptions(): array
    {
        return $this->selectOptions;
    }

    /**
     * @method removeSelectOption
     */
    public function removeSelectOption(string $key): self
    {
        if(array_key_exists($key, $this->selectOptions)) {
            unset($this->selectOptions[$key]);
        }
        return $this;
    }

    /**
     * @method clearSelectOptions
     */
    public function clearSelectOptions(): self
    {
        $this->selectOptions = [];
        return $this;
    }

    /**
     * @method setIcon
     */
    public function setIcon(?string $icon): self
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
     * @method setIconPosition
     */
    public function setIconPositionRight(bool $status = true): self
    {
        $this->iconPositionRight = $status;
        return $this;
    }

    /**
     * @method getIconPosition
     */
    public function isIconPositionRight(): string
    {
        return $this->iconPositionRight;
    }

    /**
     * @method setReadonly
     */
    public function setReadonly(bool $readonly): self
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * @method isReadonly
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    /**
     * @method setDisabled
     */
    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * @method isDisabled
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @method setValue
     */
    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @method getValue
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @method setError
     */
    public function setError(string $error): self
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @method getError
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @method setLineBreak
     */
    public function setLineBreak(bool $lineBreak): self
    {
        $this->lineBreak = $lineBreak;
        return $this;
    }

    /**
     * @method hasLineBreak
     */
    public function hasLineBreak(): bool
    {
        return $this->lineBreak;
    }

    /**
     * @method setDefaultLook
     */
    protected function setDefaultLook(): void
    {
        if(is_null($this->columnClass)) {
            $this->columnClass = 'mb-3 ';
            switch($this->type) {
                case self::TYPE_NUMBER:
                    $size = 'col-sm-4';
                    break;
                case self::TYPE_PASSWORD:
                case self::TYPE_DATE:
                    $size = 'col-sm-5';
                    break;
                default:
                    $size = 'col-sm-7';
            };
            $this->columnClass .= $size;
        }
    }
}
