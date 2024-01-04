<?php

namespace Module\Dashboard\Bundle\Flash\Modal;

use Module\Dashboard\Bundle\Flash\Flash;

class Button
{
    protected ?string $label = 'OK';
    protected ?string $className = 'btn btn-primary';
    protected ?string $callback = null;

    public function setLabel(?string $label): self
    {
        $this->label = $label ?? 'OK';
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setClassName(?string $className): self
    {
        $this->className = $className ?? 'btn btn-primary';
        return $this;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setCallback(?string $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    public function getCallback(): ?string
    {
        return $this->callback;
    }
}
