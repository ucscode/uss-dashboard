<?php

namespace Module\Dashboard\Bundle\Common;

class Promise
{
    protected mixed $value;

    public function then(callable $resolved): self
    {
        $this->value = $resolved($this->value);
        return $this;
    }
}