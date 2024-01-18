<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Interface;

use Module\Dashboard\Bundle\Crud\Component\Action;

interface InlineActionInterface
{
    public function foreachItem(array $item): Action;
}