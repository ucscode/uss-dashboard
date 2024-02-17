<?php

namespace Module\Dashboard\Bundle\Kernel\Interface;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;

interface ThemeInterface
{
    public function onload(AbstractDashboard $dashboard): void;
}