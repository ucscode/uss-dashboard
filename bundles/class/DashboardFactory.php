<?php

use PHPMailer\PHPMailer\PHPMailer;

class DashboardFactory
{
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
