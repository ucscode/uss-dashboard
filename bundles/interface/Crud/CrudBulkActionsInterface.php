<?php

interface CrudBulkActionsInterface
{
    public function onSubmit(string $action, array $selections): void;
}