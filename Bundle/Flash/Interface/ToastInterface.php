<?php

namespace Module\Dashboard\Bundle\Flash\Interface;

use Module\Dashboard\Bundle\Flash\Abstract\AbstractFlashConcept;

interface ToastInterface
{
    const POSITION_TOP = 1;
    const POSITION_RIGHT = 2;
    const POSITION_BOTTOM = 3;
    const POSITION_LEFT = 4;
    const POSITION_CENTER = 5;

    const BG_PRIMARY = 'var(--bs-primary)';
    const BG_SECONDARY = 'var(--bs-secondary)';
    const BG_WARNING = 'var(--bs-warning)';
    const BG_DANGER = 'var(--bs-danger)';
    const BG_SUCCESS = 'var(--bs-success)';
    const BG_INFO = 'var(--bs-info)';

    public function setPosition(int $yAxis, int $xAxis): AbstractFlashConcept;
    public function getPosition(): array;
    public function setDuration(int $duration): AbstractFlashConcept;
    public function getDuration(): int;
    public function setDestination(?string $destination): AbstractFlashConcept;
    public function getDestination(): ?string;
    public function enableDestinationNewWindow(bool $enabled): AbstractFlashConcept;
    public function isDestinationNewWindowEnabled(): bool;
    public function enableAutoClose(bool $enabled): AbstractFlashConcept;
    public function isAutoCloseEnabled(): bool;
    public function addStyle(string $offset, ?string $value): AbstractFlashConcept;
    public function getStyle(string $offset): ?string;
    public function removeStyle(string $offset): AbstractFlashConcept;
    public function getStyles(): array;
    public function setBackground(?string $background): AbstractFlashConcept;
    public function setClassName(?string $className): AbstractFlashConcept;
    public function getClassName(): ?string;
    public function setAvatar(?string $avatar): AbstractFlashConcept;
    public function getAvatar(): ?string;
}