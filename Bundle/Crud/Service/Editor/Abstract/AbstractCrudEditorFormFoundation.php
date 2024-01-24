<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\Interface\CrudEditorFormInterface;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;

abstract class AbstractCrudEditorFormFoundation extends AbstractDashboardForm implements CrudEditorFormInterface 
{
    public const SUBMIT_KEY = ':submit';
    public const NONCE_KEY = '__nonce';

    protected int|string|null $persistenceLastInsertId = null;
    protected bool $persistenceEnabled = true;
    protected bool $persistenceStatus = false;
    protected ?string $persistenceError = null;
    protected ?CrudEnum $persistenceType;

    protected string $nonceContext;
    protected Flash $flash;
}