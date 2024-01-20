<?php

namespace Module\Dashboard\Bundle\Crud\Kernel\Abstract;

use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Ucscode\UssElement\UssElement;

abstract class AbstractCrudKernelFoundation implements CrudKernelInterface
{
    protected UssElement $baseContainer;
    protected UssElement $widgetsContainer;
    protected UssElement $entitiesContainer;
    protected UssElement $dividerElement;
    protected array $widgets = [];
    protected bool $widgetsDisabled = false;
    protected string $primaryOffset = 'id';
    protected array $tableColumns;
}