<?php

namespace Module\Dashboard\Bundle\User\Interface;

use DateTime;
use Module\Dashboard\Bundle\Common\Password;
use Ucscode\SQuery\Condition;

interface UserRepositoryInterface
{
    public function getId(): ?int;
    public function setEmail(string $email): self;
    public function getEmail(): ?string;
    public function setUsername(string $username): self;
    public function getUsername(): ?string;
    public function setPassword(string|Password $password, bool $hash): self;
    public function getPassword(): ?string;
    public function verifyPassword(string|Password $password): bool;
    public function setRegisterTime(DateTime $dateTime): self;
    public function getRegisterTime(): ?DateTime;
    public function setUsercode(string $usercode): self;
    public function getUsercode(): ?string;
    public function setLastSeen(DateTime $dateTime): self;
    public function getLastSeen(): ?DateTime;
    public function setParent(UserInterface|int|null $parent): self;
    public function getParent(bool $getUserInstance): UserInterface|int|null;
    public function hasParent(): bool;
    public function hasChildren(): bool;
    public function getChildren(?Condition $filter): array;
    public function childrenCount(): int;
    public function getAvatar(): string;
}