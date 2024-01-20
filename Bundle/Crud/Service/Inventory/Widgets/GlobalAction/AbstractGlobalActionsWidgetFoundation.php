<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets\GlobalAction;

use Ucscode\UssForm\Form\Form;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;

class AbstractGlobalActionsWidgetFoundation
{
    public const CHECKBOX_KEY = 'checkbox:inline';
    
    protected static int $index = 0;
    protected Form $form;
    protected CrudInventoryInterface $crudInventory;

    public function __construct()
    {
        ++self::$index;
        $this->form = new Form();
        $this->form->getElement()->setAttribute('id', 'global-action-' . self::$index);
    }
}