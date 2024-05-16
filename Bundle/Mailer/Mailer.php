<?php

namespace Module\Dashboard\Bundle\Mailer;

use Module\Dashboard\Bundle\Mailer\Abstract\AbstractMailerFoundation;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Twig\TemplateWrapper;

class Mailer extends AbstractMailerFoundation
{
    public function getPHPMailer(): PHPMailer
    {
        return $this->PHPMailer;
    }

    public function getPHPMailerException(): ?Exception
    {
        return $this->PHPMailerException;
    }

    public function useMailHogTesting(bool $enabled = true): self
    {
        $this->configurePHPMailer();

        return $enabled ? $this->configureMailHogTesting() : $this->configureSMTP();
    }

    public function setTemplate(string|TemplateWrapper|null $template, ?array $context = null): self
    {
        $this->template = $template;
        $this->setContext($context ?? $this->context);
        return $this;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function setFrom(string $email, string $name = '', bool $auto = true): self
    {
        $this->PHPMailer->setFrom($email, $name, $auto);
        return $this;
    }

    public function addAddress(string $email, string $name = ''): self
    {
        $this->PHPMailer->addAddress($email, $name);
        return $this;
    }

    public function setSubject(?string $subject): self
    {
        $this->PHPMailer->Subject = $subject ?? '';
        return $this;
    }

    public function setBody(?string $body): self
    {
        $this->PHPMailer->Body = $body ?? '';
        return $this;
    }

    public function addAttachment($path, $name = '', $encoding = PHPMailer::ENCODING_BASE64, $type = '', $disposition = 'attachment'): self
    {
        $this->PHPMailer->addAttachment(
            $path,
            $name,
            $encoding,
            $type,
            $disposition
        );
        return $this;
    }

    public function sendMail(): bool
    {
        if(empty(trim($this->PHPMailer->Subject))) {
            throw new Exception("No `subject` has been defined for sending email");
        }

        if(empty(trim($this->PHPMailer->Body) && !empty($this->template))) {
            $this->PHPMailer->Body = $this->render($this->template, $this->context);
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
}
