<?php

namespace Module\Dashboard\Bundle\User\Interface;

interface UserInterface extends UserConstInterface
{
    public function isAvailable(): bool;
    public function persist(): bool;
    public function delete(): ?bool;
    public function verifyPassword(string $password): bool;
    public function getRawInfo(): array;
    public function isLonely(): bool;
    public function addComponent(string $name, UserComponentInterface $component): self;
    public function getComponent(string $name): ?UserComponentInterface;
    public function getComponents(): array;
}
