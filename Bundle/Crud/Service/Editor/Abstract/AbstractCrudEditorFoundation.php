<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Kernel\Abstract\AbstractCrudKernel;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FormManager;
use Module\Dashboard\Bundle\Crud\Service\Editor\Interface\CrudEditorInterface;
use Uss\Component\Manager\Entity;

// This defines the initial properties

abstract class AbstractCrudEditorFoundation extends AbstractCrudKernel implements CrudEditorInterface
{
    protected Entity $entity;
    protected FormManager $formManager;
    protected ?CrudEnum $lastPersistenceType = null;
    protected ?int $lastPersistenceId = null;
}