<?php

namespace Module\Dashboard\Bundle\Kernel\Compact;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Uss\Component\Kernel\Interface\UssFrameworkInterface;

final class DashboardEnvironment
{
    public function __construct(UssFrameworkInterface $system)
    {
        $system->filesystemLoader
            ->addPath(DashboardImmutable::FOUNDATION_DIR, 'Foundation');

        $system
            ->filesystemLoader
            ->addPath(DashboardImmutable::MAILS_DIR, 'Mail');

        $system
            ->filesystemLoader
            ->addPath(DashboardImmutable::THEMES_DIR, 'Theme');
    }
}
