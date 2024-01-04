<?php

namespace Module\Dashboard\Bundle\Flash\Abstract;

use Module\Dashboard\Bundle\Flash\Interface\FlashConceptInterface;

abstract class AbstractFlashConcept implements FlashConceptInterface
{
    protected ?string $message = null;
    protected ?string $title = null;
    protected array $callbacks = [];

    public function __construct()
    {

    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }
    
    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setCustomCallback(string $name, ?string $value): self
    {
        $this->callbacks[$name] = $value;
        return $this;
    }

    public function getCustomCallback(string $name): ?string
    {
        return $this->callbacks[$name] ?? null;
    }

    public function removeCustomCallback(string $name): self
    {
        if(array_key_exists($name, $this->callbacks)) {
            unset($this->callbacks[$name]);
        }
        return $this;
    }
    public function getCustomCallbacks(): array
    {
        return $this->callbacks;
    }
}
