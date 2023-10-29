<?php

use Ucscode\SQuery\SQuery;

abstract class AbstractCrudEditManager extends AbstractCrudRelativeMethods implements CrudEditInterface, CrudActionImmutableInterface
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
    protected ?array $item = null;
    protected bool $alignActionsLeft = false;
    protected ?string $itemEntityError = null;

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
     * @method setItemBy
     */
    public function setItemBy(string $key, ?string $value): CrudEditInterface
    {
        if(!is_null($value)) {
            $sQuery = (new SQuery())
                ->select('*')
                ->from($this->tablename)
                ->where($key, $value)
                ->limit(1);

            $result = Uss::instance()->mysqli->query($sQuery);
            $this->setItem($result->fetch_assoc());
        } else {
            $this->setItem(null);
        }
        return $this;
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

    /**
     * @method deleteEntity
     */
    public function deleteItemEntity(?array $item = null): bool
    {
        $item = !is_null($item) ? $item : $this->getItem();
        if($item) {
            $key = $this->getPrimaryKey();
            $value = $item[$key] ?? null;
            if(!empty($value)) {
                $sQuery = (new SQuery())
                    ->delete($this->tablename)
                    ->where($key, $value);
                $mysqli = Uss::instance()->mysqli;
                try {
                    return $mysqli->query($sQuery);
                } catch(\Exception $e) {
                    $this->itemEntityError = $e->getMessage();
                }
            }
        }
        return false;
    }

    /**
     * @method updateEntity
     */
    public function updateItemEntity(?array $item = null): bool
    {
        $this->itemEntityError = null;
        $item = is_null($item) ? $this->getItem() : $item;
        if($item) {
            $key = $this->getPrimaryKey();
            $value = $item[$key] ?? null;
            if(!empty($value)) {
                $sQuery = (new SQuery())
                    ->update($this->tablename, $item)
                    ->where($key, $value);
                try {
                    $mysqli = Uss::instance()->mysqli;
                    return $mysqli->query($sQuery);
                } catch(\Exception $e) {
                    $this->itemEntityError = $e->getMessage();
                }
            }
        }
        return false;
    }

    /**
     * @method createEntity
     */
    public function createItemEntity(?array $item = null): int|bool
    {
        $this->itemEntityError = null;
        $item = is_null($item) ? $this->getItem() : $item;
        if($item) {
            $sQuery = (new SQuery())->insert($this->tablename, $item);
            try {
                $mysqli = Uss::instance()->mysqli;
                $status = $mysqli->query($sQuery);
                return $status ? $mysqli->insert_id : false;
            } catch(\Exception $e) {
                $this->itemEntityError = $e->getMessage();
            }
        }
        return false;
    }

    /**
     * @method lastEntityError
     */
    public function lastItemEntityError(): ?string
    {
        return $this->itemEntityError;
    }
}
