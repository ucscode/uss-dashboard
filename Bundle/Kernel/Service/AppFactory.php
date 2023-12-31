<?php

namespace Module\Dashboard\Bundle\Kernel\Service;

use PHPMailer\PHPMailer\PHPMailer;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Uss\Component\Kernel\Uss;

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

    /**
     * @createPHPMailer
     */
    public function createPHPMailer(bool $exception = false): PHPMailer
    {
        $uss = Uss::instance();

        $PHPMailer = new PHPMailer($exception);
        $PHPMailer->isHTML(true);
        $PHPMailer->setFrom($uss->options->get('company:email'), $uss->options->get('company:name'));

        if($_SERVER['SERVER_NAME'] === 'localhost') {
            // Uss Dashboard use MailHog for testing
            $PHPMailer->isSMTP();
            $PHPMailer->Host = 'localhost';
            $PHPMailer->SMTPAuth = false;
            $PHPMailer->Port = 1025;
        }

        return $PHPMailer;
    }
}
