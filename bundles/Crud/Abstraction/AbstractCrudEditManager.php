<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\SQuery\SQuery;
use Ucscode\UssForm\UssFormField;

abstract class AbstractCrudEditManager extends AbstractCrudRelativeMethods implements CrudEditInterface, CrudActionImmutableInterface
{
    protected const DATASET = [
        'INTEGER' => [
            'TINYINT',
            'SMALLINT',
            'MEDIUMINT',
            'INT',
            'BIGINT'
        ],
        'FLOAT' => [
            'DECIMAL',
            'FLOAT',
            'DOUBLE',
            'REAL'
        ],
        'DATE' => [
            'DATE',
            'DATETIME',
            'TIMESTAMP',
            'TIME'
        ],
        'CHARACTER' => [
            'CHAR',
            'VARCHAR'
        ],
        'TEXT' => [
            'TINYTEXT',
            'TEXT',
            'MEDIUMTEXT',
            'LONGTEXT'
        ]
    ];

    // $actions: Buttons such as "save changes", "delete item"...
    protected array $actions = [];

    // $item: A row of data
    protected ?array $item = null;

    // $readonly: Make item not editable
    protected bool $readonly = false;

    // $alignActionsLeft: The position of actions button
    protected bool $alignActionsLeft = false;

    // $itemEntityError: The error encountered when querying the item to database
    protected ?string $itemEntityError = null;

    // $submitInterface: The modifier instance
    protected ?CrudEditSubmitInterface $submitInterface = null;

    // $domtableInterface: Readonly Modifier
    protected ?DOMTableInterface $domtableInterface = null;

    public function __construct(string $tablename)
    {
        parent::__construct($tablename);
    }

    /**
     * @method setModifier
     */
    public function setModifier(?CrudEditSubmitInterface $modifier): CrudEditInterface
    {
        $this->submitInterface = $modifier;
        return $this;
    }

    /**
     * @method getModifier
     */
    public function getModifier(): ?CrudEditSubmitInterface
    {
        return $this->submitInterface;
    }

    /**
     * @method setReadonlyModifier
     */
    public function setReadonlyModifier(?DOMTableInterface $modifier): CrudEditInterface
    {
        $this->domtableInterface = $modifier;
        return $this;
    }

    /**
     * @method getReadonlyModifier
     */
    public function getReadonlyModifier(): ?DOMTableInterface
    {
        return $this->domtableInterface;
    }

    /**
     * @method setReadonly
     */
    public function setReadOnly(bool $readonly): CrudEditInterface
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * @method isReadonly
     */
    public function isReadOnly(): bool
    {
        return $this->readonly;
    }

    /**
     * @method setField
     */
    public function setField(string $name, UssFormField $field): CrudEditInterface
    {
        $this->getEditForm()->addField($name, $field);
        return $this;
    }

    /**
     * @method getField
     */
    public function getField(string $name): ?UssFormField
    {
        return $this->getEditForm()->getField($name);
    }

    /**
     * @method removeField
     */
    public function removeField(string $name): CrudEditInterface
    {
        $this->getEditForm()->removeField($name);
        return $this;
    }

    /**
     * @method getFields
     */
    public function getFields(): array
    {
        return $this->getEditForm()->getFields();
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
        $this->getEditForm()->setAttribute('action', $url);
        return $this;
    }

    /**
     * @method getSubmitUrl
     */
    public function getSubmitUrl(): ?string
    {
        return $this->getEditForm()->getAttribute('action');
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
     * @method isAlignActionsLeft
     */
    public function isAlignActionsLeft(): bool
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
            $sQuery = (new SQuery())->insert($this->tablename, $item)->getQuery();
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
