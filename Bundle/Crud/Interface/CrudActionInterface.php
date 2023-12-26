<?php

interface CrudActionInterface
{
    public function forEachItem(array $item): CrudAction;
}
