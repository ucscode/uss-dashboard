<?php

use Ucscode\UssElement\UssElement;

abstract class AbstractCrudEditManager 
implements CrudEditInterface, CrudActionImmutableInterface
{
    protected const DATASET = [
        'integer' => [
            'TINYINT',
            'SMALLINT',
            'MEDIUMINT',
            'INT',
            'BIGINT'
        ],
        'float' => [
            'DECIMAL',
            'FLOAT',
            'DOUBLE',
            'REAL'
        ],
        'date' => [
            'DATE',
            'DATETIME',
            'TIMESTAMP',
            'TIME'
        ],
        'string' => [
            'CHAR',
            'VARCHAR'
        ],
        'text' => [
            'TINYTEXT',
            'TEXT',
            'MEDIUMTEXT',
            'LONGTEXT'
        ]
    ];

    protected string $primaryKey = 'id';
    protected ?string $submitUrl;
    protected array $fields = [];
    protected array $actions = [];
    protected array $widgets = [];
    protected ?array $item = null;
    protected bool $alignActionsLeft = false;

    public function __construct(
        protected string $tablename
    ) {
        $this->submitUrl = $_SERVER['REQUEST_URI'];
    }

    /**
     * @method setField
     */
    public function setField(string $name, CrudField $field): CrudEditInterface
    {
        $this->fields[$name] = $field;
        return $this;
    }

    /**
     * @method getField
     */
    public function getField(string $name): ?CrudField
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * @method removeField
     */
    public function removeField(string $name): CrudEditInterface
    {
        if(array_key_exists($name, $this->fields)) {
            unset($this->fields[$name]);
        }
        return $this;
    }

    /**
     * @method getFields
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @method setItem
     */
    public function setItem(?array $item): self
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @method getItem
     */
    public function getItem(?string $key = null): array|string|null
    {
        if(!is_null($this->item)) {
            if(!is_null($key)) {
                return $this->item[$key] ?? null;
            } else {
                return $this->item;
            };
        };
        return null;
    }

    /**
     * @method setAction
     */
    public function setAction(string $name, CrudAction $action): CrudEditInterface
    {
        $this->actions[$name] = $action;
        return $this;
    }

    /**
     * @method getAction
     */
    public function getAction(string $name): ?CrudAction
    {
        return $this->actions[$name] ?? null;
    }

    /**
     * @method removeAction
     */
    public function removeAction(string $name): CrudEditInterface
    {
        if(array_key_exists($name, $this->actions)) {
            unset($this->actions[$name]);
        }
        return $this;
    }

    /**
     * @method getActions
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @method setWidget
     */
    public function setWidget(string $name, UssElement $widget): CrudEditInterface
    {
        $this->widgets[$name] = $widget;
        return $this;
    }

    /**
     * @method getWidget
     */
    public function getWidget(string $name): ?UssElement
    {
        return $this->widgets[$name] ?? null;
    }

    /**
     * @method removeWidget
     */
    public function removeWidget(string $name): CrudEditInterface
    {
        if(array_key_exists($name, $this->widgets)) {
            unset($this->widgets[$name]);
        }
        return $this;
    }

    /**
     * @method getWidgets
     */
    public function getWidgets(): array
    {
        return $this->widgets;
    }

    /**
     * @method setPrimaryKey
     */
    public function setPrimaryKey(string $key): CrudEditInterface
    {
        $this->primaryKey = $key;
        return $this;
    }

    /**
     * @method getPrimaryKey
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @method setSubmitUrl
     */
    public function setSubmitUrl(?string $url): CrudEditInterface
    {
        $this->submitUrl = $url;
        return $this;
    }

    /**
     * @method getSubmitUrl
     */
    public function getSubmitUrl(): ?string
    {
        return $this->submitUrl;
    }

    /**
     * @method setAlignActionsLeft
     */
    public function setAlignActionsLeft(bool $status = true): self
    {
        $this->alignActionsLeft = $status;
        return $this;
    }

    /**
     * @method getAlignActionsLeft
     */
    public function getAlignActionsLeft(): bool
    {
        return $this->alignActionsLeft;
    }
}
