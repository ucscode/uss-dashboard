<?php

namespace Module\Dashboard\Bundle\Crud\Compact\Abstract;

use Module\Dashboard\Bundle\Crud\Compact\Interface\CrudKernelInterface;
use Uss\Component\Kernel\Uss;

abstract class AbstractCrudKernel implements CrudKernelInterface
{
    protected array $tableColumns;

    public function __construct(public readonly string $tableName) 
    {
        $uss = Uss::instance();
        $this->tableColumns = array_values($uss->getTableColumns($this->tableName));
    }
}