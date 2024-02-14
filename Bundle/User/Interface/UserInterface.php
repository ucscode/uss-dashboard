<?php

namespace Module\Dashboard\Bundle\User\Interface;

use Uss\Component\Common\Entity;

interface UserInterface extends UserRepositoryInterface, UserConstInterface
{
    public function isAvailable(): bool;
    public function persist(): bool;
    public function delete(): ?bool;
    public function getEntity(): Entity;
    public function isLonely(): bool;
    public function saveToSession(): self;
    public function acquireFromSession(): self;
    public function destroySession(): self;
    public function allocate(string $key, string $value): self;
    public function setParentByReferralLink(): bool;
}
