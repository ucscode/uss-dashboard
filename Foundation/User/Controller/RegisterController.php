<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Exception;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Route\RouteInterface;

class RegisterController implements RouteInterface
{
    public function onload(array $regex)
    {
        $dashboard = UserDashboard::instance();
        $dashboard->enableFirewall(false);

        $document = $dashboard->getDocument('register');
        $form = $document->getCustom('app.form');

        if(!($form instanceof DashboardFormInterface)) {
            throw new Exception(
                sprintf(
                    "Dashboard application registration form must be an instance of %",
                    DashboardFormInterface::class
                )
            );
        }

        $form->handleSubmission();
        $form->build();
        
        $dashboard->render($document->getTemplate(), ['form' => $form]);
    }

}
