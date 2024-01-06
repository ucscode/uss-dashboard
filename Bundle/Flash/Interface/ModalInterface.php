<?php

namespace Module\Dashboard\Bundle\Flash\Interface;

use Module\Dashboard\Bundle\Flash\Modal\Button;

interface ModalInterface
{
    public const DEFAULT_BUTTON = 'cancel';
    
    public function addButton(string $name, Button $button): self;
    public function getButton(string $name): ?Button;
    public function removeButton(string $name): self;
    public function getButtons(): array;
    public function setSize(?string $name): self;
    public function getSize(): string;
    public function enableCloseButton(bool $enabled = true): self;
    public function isCloseButtonEnabled(): bool;
    public function enableBackdrop(bool $enabled = true): self;
    public function isBackdropEnabled(): bool;
    public function enableBackdropStatic(bool $enabled = true): self;
    public function isBackdropStaticEnabled(): bool;
    public function enableKeyboard($enabled = true): self;
    public function isKeyboardEnabled(): bool;
}
