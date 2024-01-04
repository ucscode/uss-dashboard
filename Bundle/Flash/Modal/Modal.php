<?php

namespace Module\Dashboard\Bundle\Flash\Modal;

use Module\Dashboard\Bundle\Flash\Abstract\AbstractFlashConcept;
use Module\Dashboard\Bundle\Flash\Interface\ModalInterface;

class Modal extends AbstractFlashConcept implements ModalInterface
{
    protected array $buttons = [];
    protected string $size = 'normal';
    protected bool $closeButtonEnabled = true;
    protected bool $backdropEnabled = true;
    protected bool $backdropStaticEnabled = true;
    protected bool $keyboardEnabled = true;
    protected ?string $onEscapeCallback = null;
    protected int $delay = 0;

    public function __construct()
    {
        parent::__construct();
        $this->addButton(self::DEFAULT_BUTTON, new Button());
    }

    public function setSize(?string $size): self
    {
        $this->size = $size ?? 'normal';
        return $this;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function addButton(string $name, Button $button): self
    {
        $this->buttons[$name] = $button;
        return $this;
    }

    public function getButton(string $name): ?Button
    {
        return $this->buttons[$name] ?? null;
    }

    public function removeButton(string $name): self
    {
        if(array_key_exists($name, $this->buttons)) {
            unset($this->buttons[$name]);
        }
        return $this;
    }

    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function enableCloseButton(bool $enabled = true): self
    {
        $this->closeButtonEnabled = $enabled;
        return $this;
    }

    public function isCloseButtonEnabled(): bool
    {
        return $this->closeButtonEnabled;
    }

    public function enableBackdrop(bool $enabled = true): self
    {
        $this->backdropEnabled = $enabled;
        return $this;
    }

    public function isBackdropEnabled(): bool
    {
        return $this->backdropEnabled;
    }

    public function enableBackdropStatic(bool $enabled = true): self
    {
        $this->backdropStaticEnabled = $enabled;
        return $this;
    }

    public function isBackdropStaticEnabled(): bool
    {
        return $this->backdropStaticEnabled;
    }

    public function enableKeyboard($enabled = true): self
    {
        $this->keyboardEnabled = $enabled;
        return $this;
    }

    public function isKeyboardEnabled(): bool
    {
        return $this->keyboardEnabled;
    }

    public function setDelay(?int $delay): self
    {
        $this->delay = $delay === null ? 0 : abs($delay);
        return $this;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }
}
