<?php

namespace Module\Dashboard\Bundle\Kernel\Interface;

interface DashboardThemeInterface
{
    public function onload(DashboardInterface $dashboard): void;
}