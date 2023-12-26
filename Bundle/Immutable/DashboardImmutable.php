<?php

namespace Module\Dashboard\Bundle\Immutable;

final class DashboardImmutable
{
    public const GITHUB_REPO = 'https://github.com/ucscode/uss-dashboard';
    public const BASE_DIR = USS_DASHBOARD_DIR;
    public const BUNDLE_DIR = self::BASE_DIR . '/Bundle';
    public const FOUNDATION_DIR = self::BASE_DIR . "/Foundation";
    public const GUI_DIR = self::BASE_DIR . '/GUI';
    public const ASSETS_DIR = self::GUI_DIR . "/assets";
    public const THEMES_DIR = self::GUI_DIR . "/themes";
    public const MAILS_DIR = self::GUI_DIR . '/mails';
}
