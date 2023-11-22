<?php

interface AdminDashboardInterface
{
    public const DIR = DashboardImmutable::SRC_DIR . '/Admin';
    public const ASSETS_DIR = self::DIR . '/assets';
    public const FORM_DIR = self::DIR . '/forms';
    public const TEMPLATE_DIR = self::DIR . '/templates';
    public const CONTROLLER_DIR = self::DIR . '/controllers';
}