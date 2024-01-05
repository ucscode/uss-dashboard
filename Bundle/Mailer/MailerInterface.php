<?php

namespace Module\Dashboard\Bundle\Mailer;

use PHPMailer\PHPMailer\PHPMailer;

interface MailerInterface
{
    public function getPHPMailer(): PHPMailer;
    public function setTemplate(string $template, ?array $context): self;
    public function getTemplateOutput(): string;
    public function setContext(array $context): self;
    public function useMailHogTesting(bool $enabled = true): self;
    public function setFrom(string $email, string $name, bool $auto): self;
    public function addAddress(string $email, string $name): self;
    public function setSubject(?string $subject): self;
    public function setBody(?string $body): self;
    public function addAttachment($path, $name = '', $encoding = PHPMailer::ENCODING_BASE64, $type = '', $disposition = 'attachment'): self;
}
