<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Route\RouteInterface;

class RegisterController implements RouteInterface
{
    public function onload(array $regex)
    {
        $dashboard = UserDashboard::instance();
        $dashboard->enableFirewall(false);
        $document = $dashboard->getDocument('register');
        $form = $document->getCustom('register:form');
        $form->handleSubmission();
        $dashboard->render($document->getTemplate(), ['form' => $form]);
    }

}
