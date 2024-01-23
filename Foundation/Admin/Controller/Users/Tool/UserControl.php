<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Tool;

use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Ucscode\UssForm\Gadget\Gadget;

class UserControl
{
    public function __construct(protected CrudEditor $crudEditor)
    {}

    public function iterateRolesGadget(callable $callback, $filter = false): void
    {
        $roleField = $this->crudEditor->getForm()->getCollection('roles')->getField('roles');
        $callback($roleField->getElementContext()->gadget);
        foreach($roleField->getGadgets() as $gadget) {
            if($filter && !$gadget->widget->hasAttribute('data-role')) {
                continue;
            }
            $callback($gadget);
        }
    }

    public function autoCheckRolesCheckbox(?array $roles = null): void
    {
        $this->iterateRolesGadget(function(Gadget $gadget) use ($roles) {
            $role = $gadget->widget->getAttribute('data-role');
            $gadget->widget->setChecked(in_array($role, $roles));
        }, true);
    }
}