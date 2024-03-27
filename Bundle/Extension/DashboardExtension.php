<?php

namespace Module\Dashboard\Bundle\Extension;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class DashboardExtension extends AbstractExtension implements GlobalsInterface
{
    public const ACCESS_KEY = '__dashboard';

    public function getGlobals(): array
    {
        return [self::ACCESS_KEY => ExtensionModel::instance()];
    }
}
