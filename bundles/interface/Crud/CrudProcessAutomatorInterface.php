<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\UssElement\UssElement;

interface CrudProcessAutomatorInterface
{
    public function processBulkActions(): void;

    public function processIndexAction(): void;

    public function processCreateAction(): void;

    public function processReadAction(): void;

    public function processUpdateAction(): void;

    public function processDeleteAction(): void;

    public function processAllActions(): void;

    public function getCreatedUI(): ?UssElement;

    public function getCrudIndexManager(): CrudIndexManager;

    public function getCrudEditManager(): CrudEditManager;

    public function getCurrentAction(): string;
}