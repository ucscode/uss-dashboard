<?php

namespace Module\Dashboard\Bundle\Kernel\Service\Interface;

interface AppControlInterface
{
    public function getBase(): string;
    public function getThemeFolder(): string;
    public function getPermissions(): array;
}