<?php

use PHPMailer\PHPMailer\PHPMailer;

class DashboardFactory
{
    private static array $projects = [];

    public static function registerProject(DashboardInterface $dashboard): void
    {
        if(!in_array($dashboard, self::$projects, true)) {
            self::$projects[] = $dashboard;
        }
    }

    /**
     * @method getProjects
     */
    public function getProjects(): array
    {
        return self::$projects;
    }

    /**
     * @method getPermissions
     */
    public function getPermissions(): array
    {
        $permissions = [];
        foreach($this->getProjects() as $dashboard) {
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
