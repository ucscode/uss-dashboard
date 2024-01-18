<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Action;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\InlineActionInterface;

class InlineEditAction implements InlineActionInterface
{
    public function foreachItem(array $item): Action
    {
        $action = (new Action())
            ->addClass("btn btn-primary")
            ->setContent("<i class='bi bi-pen'></i> Edit")
        ;
        return $action;
    }
}