<?php

namespace Module\Dashboard\Bundle\User\Service;

use Module\Dashboard\Bundle\User\User;
use Ucscode\Pairs\Pairs;

class Meta
{
    public function __construct(
        protected User $user,
        protected Pairs $pairs
    ){}

    public function set(string $key, mixed $value): bool
    {
        return $this->pairs->set($key, $value, $this->user->getId());
    }

    public function get(string $key, bool $epoch = false): mixed
    {
        return $this->pairs->get($key, $this->user->getId(), $epoch);
    }

    public function remove(string $key): bool
    {
        return $this->pairs->remove($key, $this->user->getId());
    }

    public function getAll(?string $like = null): array
    {
        return $this->pairs->getSequence($this->user->getId(), $like);
    }
}