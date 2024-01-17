<?php

namespace Module\Dashboard\Bundle\Kernel\Service;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;

class AppFactory
{
    private static array $apps = [];

    public static function registerApp(DashboardInterface $dashboard): void
    {
        if(!in_array($dashboard, self::$apps, true)) {
            if(!isset($dashboard->appControl)) {
                throw new \Exception(
                    sprintf("Cannot register app that does not incorporate '%s' instance", AppControl::class)
                );
            }
            self::$apps[] = $dashboard;
        }
    }
    
    public function getApps(): array
    {
        return self::$apps;
    }

    /**
     * @method getPermissions
     */
    public function getPermissions(): array
    {
        $permissions = [];
        foreach($this->getApps() as $dashboard) {
            $permissions = array_merge($permissions, $dashboard->config->getPermissions());
        }
        sort($permissions);
        return array_unique($permissions);
    }
}
