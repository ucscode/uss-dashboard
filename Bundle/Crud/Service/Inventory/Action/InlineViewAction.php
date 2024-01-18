<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Action;

use Module\Dashboard\Bundle\Crud\Component\Action;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\InlineActionInterface;

class InlineViewAction implements InlineActionInterface
{
    public function foreachItem(array $item): Action
    {
        return (new Action())
            ->addClass("btn-secondary btn-Node btnova btn_plot")
            ->setContent("<i class='bi bi-eye'></i> View")
        ;
    }
}