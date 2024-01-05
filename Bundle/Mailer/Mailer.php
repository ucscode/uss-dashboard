<?php

namespace Module\Dashboard\Bundle\Mailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer extends AbstractMailer implements MailerInterface
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
        return $enabled ?
            $this->configureMailHogTesting() :
            $this->configureSMTP();
    }

    public function setTemplate(string $template, ?array $context = null): self
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
}
