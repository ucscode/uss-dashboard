<?php

namespace Module\Dashboard\Bundle\Mailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Ucscode\Pairs\Pairs;
use Uss\Component\Kernel\Uss;

abstract class AbstractMailer
{
    protected bool $isLocalhost;
    protected bool $mailHogEnabled = false;
    protected ?string $template = '@Mail/classic/base.html.twig';
    protected array $context = [];
    protected ?string $templateUrl = null;
    protected PHPMailer $PHPMailer;
    
    public function __construct()
    {
        $memory = Uss::instance()->options;
        $this->checkEnvironment();
        $this->configurePHPMailer($memory);
    }

    public function getTemplateOutput(): string
    {
        return Uss::instance()->render(
            $this->template, 
            $this->context + [
                'template_url' => $this->templateUrl
            ], 
            true
        );
    }

    protected function checkEnvironment(): void
    {
        $this->isLocalhost = in_array(
            $_SERVER['SERVER_NAME'],
            ['localhost', '127.0.0.1', '::1'],
            true
        );
    }

    protected function configurePHPMailer(Pairs $memory): void
    {
        $smtpEnabled = $memory->get('smtp:state') != "default";

        $this->PHPMailer = new PHPMailer(true);
        $this->PHPMailer->isHTML(true);
        $this->PHPMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $this->PHPMailer->setFrom(
            $memory->get('company:email'),
            $memory->get('company:name')
        );

        if($this->mailHogEnabled) {
            $this->PHPMailer->isSMTP(true);
            $this->PHPMailer->SMTPAuth = false;
            $this->PHPMailer->Host = 'localhost';
            $this->PHPMailer->Port = 1025;
            return;
        }

        if($smtpEnabled) {
            $this->PHPMailer->isSMTP($smtpEnabled);
            $this->PHPMailer->SMTPAuth = true;
            $this->PHPMailer->Host = $memory->get('company:server');
            $this->PHPMailer->Username = $memory->get('company:username');
            $this->PHPMailer->Password = $memory->get('company:password');
            $this->PHPMailer->Port = $memory->get('smtp:port');
            $this->PHPMailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->PHPMailer->SMTPDebug = $this->isLocalhost ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
        }
    }
}
