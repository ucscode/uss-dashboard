<?php

namespace Module\Dashboard\Bundle\Mailer;

use Exception;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Ucscode\Pairs\Pairs;
use Uss\Component\Kernel\Uss;

abstract class AbstractMailer
{
    protected bool $isLocalhost;
    protected ?string $template = '@Mail/classic/base.html.twig';
    protected array $context = [];
    protected PHPMailer $PHPMailer;
    protected ?PHPMailerException $PHPMailerException = null;

    public function __construct()
    {
        $this->PHPMailer = new PHPMailer(true);
        $this->PHPMailer->isHTML(true);
        $this->checkEnvironment();
        $this->configurePHPMailer();
        $this->configureSMTP();
    }

    public function getTemplateOutput(): string
    {
        return Uss::instance()->render(
            $this->template,
            $this->context,
            true
        );
    }

    public function sendMail(): bool
    {
        if(empty(trim($this->PHPMailer->Subject))) {
            throw new Exception("No `subject` has been defined for sending email");
        }

        if(empty(trim($this->PHPMailer->Body) && !empty($this->template))) {
            $this->PHPMailer->Body = $this->getTemplateOutput();
        }
        
        if(empty($this->PHPMailer->Body)) {
            throw new Exception("No `template` or `body` has been defined for sending email");
        }

        if(empty($this->PHPMailer->getToAddresses())) {
            throw new Exception("No email address has been added for sending email");
        }

        try {
            
            return $this->PHPMailer->send();

        } catch(Exception $PHPMailerException) {

            $this->PHPMailerException = $PHPMailerException;

            return false;

        }
    }

    protected function checkEnvironment(): void
    {
        $this->isLocalhost = in_array(
            $_SERVER['SERVER_NAME'],
            ['localhost', '127.0.0.1', '::1'],
            true
        );
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
        $smtpEnabled = $memory->get('smtp:state') != "default";

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
