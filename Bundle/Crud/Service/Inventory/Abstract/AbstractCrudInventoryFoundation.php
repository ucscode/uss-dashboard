<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Kernel\Abstract\AbstractCrudKernel;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Ucscode\DOMTable\DOMTable;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;

abstract class AbstractCrudInventoryFoundation extends AbstractCrudKernel implements CrudInventoryInterface
{
    protected array $globalActions = [];
    protected array $inlineActions = [];
    protected bool $globalActionsDisabled = false;
    protected bool $inlineActionDisabled = false;
    protected bool $inlineActionDropdownActive = true;
    protected array $entityMutationIterators = [];
    protected DOMTable $domTable;
    protected SQuery $sQuery;
    protected UssElement $paginatorContainer;
}