<?php

namespace Module\Dashboard\Foundation\Admin;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;

interface AdminDashboardInterface extends DashboardInterface
{
    public const DIR = DashboardImmutable::ADMIN_DIR;
    public const FORM_DIR = self::DIR . '/Form';
    public const CONTROLLER_DIR = self::DIR . '/Controller';
    public const COMPACT_DIR = self::DIR . '/Compact';
    public const TEMPLATE_DIR =  self::DIR . '/Template';
}
