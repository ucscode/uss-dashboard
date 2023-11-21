<?php

interface UserDashboardInterface 
{
    public const DIR = DashboardImmutable::SRC_DIR . '/User';
    public const ASSETS_DIR = self::DIR . '/assets';
    public const FORMS_DIR = self::DIR . '/forms';
    public const CONTROLLER_DIR = self::DIR . '/controllers';
    public const TEMPLATE_DIR = self::DIR . "/templates";
}