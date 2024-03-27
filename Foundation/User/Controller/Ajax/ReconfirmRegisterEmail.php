<?php

namespace Module\Dashboard\Foundation\User\Controller\Ajax;

use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Service\EmailResolver;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Kernel\Uss;
use Uss\Component\Route\RouteInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class ReconfirmRegisterEmail implements RouteInterface
{
    protected Uss $uss;

    public function onload(ParameterBag $container): Response
    {
        $this->uss = Uss::instance();

        $summary = "Request security could not be verified";
        $status = false;

        $isValidNonce = $this->uss->nonce('__dashboard', $_POST['nonce'] ?? '#');

        if($isValidNonce) {

            $summary = "The email account was not found";

            $email = Uss::instance()->sanitize($_POST['email'] ?? null);
            $user = (new User())->allocate('email', $email);

            if($user->isAvailable()) {

                $summary = "The email has already been confirmed";

                if($user->meta->get('verify-email:code') !== null) {

                    $registerDocument = UserDashboard::instance()->getDocument('register');
                    $registerFormProperties = $registerDocument->getCustom('app.form')->getProperties();

                    $emailResolver = new EmailResolver($registerFormProperties);
                    $status = $emailResolver->sendConfirmationEmail($user);
                    $summary = $emailResolver->getConfirmationEmailSummary($status);
                }
            }
        }

        $this->uss->terminate($status, $summary);
    }
}
