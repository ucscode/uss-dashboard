<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Compact\Abstract\AbstractCrudKernel;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FormManager;
use Module\Dashboard\Bundle\Crud\Service\Editor\Interface\CrudEditorInterface;

abstract class AbstractCrudEditorFoundation extends AbstractCrudKernel implements CrudEditorInterface
{
    protected bool $mutated = false;
    protected array $entity = [];
    protected FormManager $formManager;
}