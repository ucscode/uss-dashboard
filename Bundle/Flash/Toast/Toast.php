<?php

namespace Module\Dashboard\Bundle\Flash\Toast;

use Module\Dashboard\Bundle\Flash\Abstract\AbstractFlashConcept;
use Module\Dashboard\Bundle\Flash\Interface\FlashConceptInterface;
use Module\Dashboard\Bundle\Flash\Interface\ToastInterface;

class Toast extends AbstractFlashConcept implements ToastInterface
{
    protected int $yAxis = self::POSITION_TOP;
    protected int $xAxis = self::POSITION_RIGHT;
    protected int $duration = 6000;
    protected ?string $destination = null;
    protected bool $newWindow = true;
    protected bool $autoCloseEnabled = false;
    protected array $styles = [
        'background' => self::BG_PRIMARY,
    ];
    protected ?string $className = null;
    protected ?string $avatar = null;

    public function __construct()
    {
        parent::__construct();
        // code here
    }

    public function setPosition(int $yAxis, int $xAxis = self::POSITION_RIGHT): AbstractFlashConcept
    {
        $this->yAxis = $yAxis;
        $this->xAxis = $xAxis;
        return $this;
    }

    public function getPosition(): array
    {
        return [
            'yAxis' => $this->yAxis,
            'xAxis' => $this->xAxis,
        ];
    }

    public function setDuration(int $duration): AbstractFlashConcept
    {
        $this->duration = $duration;
        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDestination(?string $destination): AbstractFlashConcept
    {
        $this->destination = $destination;
        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function enableDestinationNewWindow(bool $enabled): AbstractFlashConcept
    {
        $this->newWindow = $enabled;
        return $this;
    }

    public function isDestinationNewWindowEnabled(): bool
    {
        return $this->newWindow;
    }

    public function enableAutoClose(bool $enabled): AbstractFlashConcept
    {
        $this->autoCloseEnabled = $enabled;
        return $this;
    }

    public function isAutoCloseEnabled(): bool
    {
        return $this->autoCloseEnabled;
    }

    public function addStyle(string $offset, ?string $value): AbstractFlashConcept
    {
        $this->styles[$offset] = $value;
        return $this;
    }

    public function getStyle(string $offset): ?string
    {
        return $this->styles[$offset] ?? null;
    }
    
    public function removeStyle(string $offset): AbstractFlashConcept
    {
        if(array_key_exists($offset, $this->styles)) {
            unset($this->styles[$offset]);
        }
        return $this;
    }

    public function getStyles(): array
    {
        return $this->styles;
    }

    public function setBackground(?string $background): AbstractFlashConcept
    {
        $this->styles['background'] = $background ?? self::BG_PRIMARY;
        return $this;
    }

    public function setClassName(?string $className): AbstractFlashConcept
    {
        $this->className = $className;
        return $this;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setAvatar(?string $avatar): AbstractFlashConcept
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }
}