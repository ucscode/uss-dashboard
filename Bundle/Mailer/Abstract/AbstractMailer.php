<?php

namespace Module\Dashboard\Bundle\Mailer\Abstract;

use Exception;

abstract class AbstractMailer extends AbstractMailerFoundation
{
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
