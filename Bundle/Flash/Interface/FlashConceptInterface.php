<?php

namespace Module\Dashboard\Bundle\Flash\Interface;

interface FlashConceptInterface
{
    public function setMessage(?string $content): self;
    public function getMessage(): ?string;
    public function setTitle(?string $heading): self;
    public function getTitle(): ?string;
    public function setCustomCallback(string $name, ?string $value): self;
    public function getCustomCallback(string $name): ?string;
    public function removeCustomCallback(string $name): self;
    public function getCustomCallbacks(): array;
}
