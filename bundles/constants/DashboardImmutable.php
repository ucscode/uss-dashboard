<?php

final class DashboardImmutable
{
    public const GITHUB_REPO = 'https://github.com/ucscode/uss-dashboard';

    public const BASE_DIR = UD_DIR;
    public const ASSETS_DIR = self::BASE_DIR . "/assets";
    public const SRC_DIR = self::BASE_DIR . "/src";
    public const VIEW_DIR = self::BASE_DIR . "/view";
    public const RES_DIR = self::BASE_DIR . "/bundles";
    public const CLASS_DIR = self::RES_DIR . "/class";
    public const CENTRAL_DIR = self::RES_DIR . "/central";
    public const THEME_DIR = self::BASE_DIR . "/themes";
    public const MAIL_TEMPLATE_DIR = self::SRC_DIR . '/mail-templates';
}
