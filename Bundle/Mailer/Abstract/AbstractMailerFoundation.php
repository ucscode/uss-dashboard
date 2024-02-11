<?php

namespace Module\Dashboard\Bundle\Mailer\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractSandbox;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use Uss\Component\Kernel\Uss;

abstract class AbstractMailerFoundation extends AbstractSandbox
{
    protected array $context = [];
    protected ?string $template = null;
    protected PHPMailer $PHPMailer;
    protected ?PHPMailerException $PHPMailerException = null;

    public function __construct()
    {        
        parent::__construct();
        $this->PHPMailer = new PHPMailer(true);
        $this->PHPMailer->isHTML(true);
        $this->configurePHPMailer();
        $this->configureSMTP();
    }

    protected function configurePHPMailer(): self
    {
        $memory = Uss::instance()->options;

        $this->PHPMailer->isSMTP(false);
        $this->PHPMailer->SMTPAuth = false;
        $this->PHPMailer->Host = "localhost";
        $this->PHPMailer->Port = 25;
        $this->PHPMailer->Username = "";
        $this->PHPMailer->Password = "";
        $this->PHPMailer->SMTPSecure = "";
        $this->PHPMailer->SMTPDebug = SMTP::DEBUG_OFF;

        $this->PHPMailer->setFrom(
            $memory->get('company:email'),
            $memory->get('company:name')
        );

        return $this;
    }

    protected function configureSMTP(): self
    {
        $memory = Uss::instance()->options;
        $smtpEnabled = !empty($memory->get('smtp:enabled'));

        if($smtpEnabled) {
            $this->PHPMailer->isSMTP(true);
            $this->PHPMailer->SMTPAuth = true;
            $this->PHPMailer->Host = $memory->get('company:server');
            $this->PHPMailer->Port = $memory->get('smtp:port');
            $this->PHPMailer->Username = $memory->get('company:username');
            $this->PHPMailer->Password = $memory->get('company:password');
            $this->PHPMailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->PHPMailer->SMTPDebug = $this->isLocalhost ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
        }

        return $this;
    }

    protected function configureMailHogTesting(): self
    {
        $this->PHPMailer->isSMTP(true);
        $this->PHPMailer->SMTPAuth = false;
        $this->PHPMailer->Host = 'localhost';
        $this->PHPMailer->Port = 1025;
        return $this;
    }
}