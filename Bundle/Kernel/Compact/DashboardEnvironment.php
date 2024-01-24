<?php

namespace Module\Dashboard\Bundle\Kernel\Compact;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Uss\Component\Kernel\Interface\UssFrameworkInterface;

class DashboardEnvironment
{
    public function __construct(UssFrameworkInterface $framework)
    {
        $framework->filesystemLoader->addPath(DashboardImmutable::FOUNDATION_DIR, 'Foundation');
        $framework->filesystemLoader->addPath(DashboardImmutable::MAILS_DIR, 'Mail');
        $framework->filesystemLoader->addPath(DashboardImmutable::THEMES_DIR, 'Theme');
    }
}
