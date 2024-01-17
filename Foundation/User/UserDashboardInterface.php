<?php

namespace Module\Dashboard\Foundation\User;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;

interface UserDashboardInterface
{
    public const DIR = DashboardImmutable::USER_DIR;
    public const FORM_DIR = self::DIR . '/Form';
    public const CONTROLLER_DIR = self::DIR . '/Controller';
    public const COMPACT_DIR = self::DIR . '/Compact';
    public const TEMPLATE_DIR = self::DIR . '/Template';
}
