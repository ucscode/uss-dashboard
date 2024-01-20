<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract;

use Module\Dashboard\Bundle\Crud\Kernel\Abstract\AbstractCrudKernel;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Form\Form;

abstract class AbstractCrudInventoryFoundation extends AbstractCrudKernel implements CrudInventoryInterface
{
    protected UssElement $globalActionsContainer;
    protected array $globalActions = [];
    protected array $inlineActions = [];
    protected bool $globalActionsDisabled = false;
    protected bool $inlineActionDisabled = false;
    protected bool $inlineActionDropdownActive = true;
    protected DOMTable $domTable;
    protected ?DOMTableIteratorInterface $itemsMutationIterator = null;
    protected SQuery $sQuery;
    protected UssElement $paginatorContainer;
    protected Form $globalActionForm;
}