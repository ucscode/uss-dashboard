<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Compact\Abstract\AbstractCrudKernel;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;

abstract class AbstractCrudInventoryFoundation extends AbstractCrudKernel
{
    protected bool $inlineActionDisabled = false;
    protected bool $inlineActionDropdownActive = true;
    protected array $inlineActions = [];
    protected DOMTable $domTable;
    protected ?DOMTableIteratorInterface $itemsMutationIterator = null;
    protected SQuery $sQuery;
    protected UssElement $paginatorContainer;
}