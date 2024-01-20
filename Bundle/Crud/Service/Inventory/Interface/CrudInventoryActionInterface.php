<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Interface;

use Module\Dashboard\Bundle\Crud\Component\Action;

interface CrudInventoryActionInterface
{
    public function setInlineAction(string $name, InlineActionInterface $action): self;
    public function getInlineAction(string $name): ?InlineActionInterface;
    public function removeInlineAction(string $name): self;
    public function getInlineActions(): array;
    public function disableInlineAction(bool $enabled): self;
    public function isInlineActionDisabled(): bool;
    public function setInlineActionAsDropdown(bool $status): self;
    public function isInlineActionAsDropdown(): bool;
    public function setGlobalAction(string $name, Action $action): self;
    public function getGlobalAction(string $name): ?Action;
    public function removeGlobalAction(string $name): self;
    public function disableGlobalActions(bool $status): self;
    public function isGlobalActionsDisabled(): bool;
    public function getGlobalActions(): array;
}