<?php

namespace Module\Dashboard\Bundle\Crud\Compact\Abstract;

use Module\Dashboard\Bundle\Crud\Compact\Interface\CrudKernelInterface;
use Ucscode\UssElement\UssElement;

abstract class AbstractCrudKernelFoundation implements CrudKernelInterface
{
    protected UssElement $baseContainer;
    protected UssElement $widgetsContainer;
    protected UssElement $actionsContainer;
    protected UssElement $entitiesContainer;
    protected array $widgets = [];
    protected array $actions = [];
    protected bool $actionsDisabled = false;
    protected bool $widgetsDisabled = false;
    protected array $tableColumns;

}