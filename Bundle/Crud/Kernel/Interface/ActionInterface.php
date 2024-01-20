<?php

namespace Module\Dashboard\Bundle\Crud\Kernel\Interface;

use Ucscode\UssElement\UssElement;

interface ActionInterface
{
    public function setAsButtonNode(): self;
    public function setAsAnchorNode(): self;
    public function isButtonNode(): bool;
    public function isAnchorNode(): bool;
    public function setContent(null|string|UssElement $content): self;
    public function getContent(): null|string|UssElement;
    public function setDisabled(bool $disabled): self;
    public function isDisabled(): bool;
    public function addClass(?string $className): self;
    public function removeClass(string $className): self;
    public function getElement(): UssElement;
    public function setAttribute(string $name, ?string $value, bool $append): self;
    public function getAttribute(string $name): ?string;
    public function removeAttribute(string $name, ?string $value): self;
    public function setValue(?string $value): self;
    public function getValue(): ?string;
}