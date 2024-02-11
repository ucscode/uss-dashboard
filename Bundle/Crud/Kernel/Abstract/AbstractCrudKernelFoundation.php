<?php

namespace Module\Dashboard\Bundle\Crud\Kernel\Abstract;

use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Ucscode\UssElement\UssElement;
use Uss\Component\Kernel\Uss;

abstract class AbstractCrudKernelFoundation implements CrudKernelInterface
{
    public readonly array $tableColumns;
    public readonly array $tableColumnsLabelled;

    protected UssElement $baseContainer;
    protected UssElement $widgetsContainer;
    protected UssElement $entitiesContainer;
    protected UssElement $dividerElement;

    protected string $primaryOffset = 'id';
    protected array $widgets = [];
    protected bool $widgetsDisabled = false;

    public function __construct(public readonly string $tableName) 
    {
        $this->tableColumns = Uss::instance()->getTableColumns($this->tableName);
        $this->defineTableColumnLabels();
    }

    protected function defineTableColumnLabels(): void
    {
        $keys = array_keys($this->tableColumns);
        $labels = array_map(function($column) {
            return ucfirst(str_replace("_", " ", $column));
        }, $keys);
        $this->tableColumnsLabelled = array_combine($keys, $labels);
    }
}