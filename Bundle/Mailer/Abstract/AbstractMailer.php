<?php

namespace Module\Dashboard\Bundle\Mailer\Abstract;

use Exception;
use Uss\Component\Kernel\Uss;

abstract class AbstractMailer extends AbstractMailerFoundation
{
    public function getRenderContent(): string
    {
        $this->borrowedExtension->configureRenderContext();
        $this->context += Uss::instance()->twigContext;
        return $this->twigEnvironment->render($this->template, $this->context);
    }

    public function sendMail(): bool
    {
        if(empty(trim($this->PHPMailer->Subject))) {
            throw new Exception("No `subject` has been defined for sending email");
        }

        if(empty(trim($this->PHPMailer->Body) && !empty($this->template))) {
            $this->PHPMailer->Body = $this->getRenderContent();
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
