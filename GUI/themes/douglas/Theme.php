<?php

namespace Module\Dashboard\GUI\themes\douglas;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardThemeInterface;
use Uss\Component\Block\BlockManager;

class Theme implements DashboardThemeInterface
{
    public function onload(DashboardInterface $dashboard): void
    {
        var_dump(BlockManager::instance());
    }
}