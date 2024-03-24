<?php

namespace Module\Dashboard\Bundle\Kernel\Service\Interface;

interface AppControlInterface
{
    public function getUrlBasePath(): string;
    public function getThemeFolder(): string;
    public function getPermissions(): array;
    public function getThemeConfig(): array;
}