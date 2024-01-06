<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Exception;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Route\RouteInterface;

class RecoveryController implements RouteInterface
{
    public function onload($pageInfo)
    {
        $dashboard = UserDashboard::instance()->enableFirewall(false);
        $document = $dashboard->getDocument('recovery');

        $form = $document->getCustom('app.form');

        if(!$form instanceof DashboardFormInterface) {
            throw new Exception(
                sprintf(
                    "Dashboard application recovery form must be an instance of %",
                    DashboardFormInterface::class
                )
            );
        }

        $form->build();
        $form->handleSubmission();

        $dashboard->render($document->getTemplate(), [
            'form' => $form
        ]);
    }
}
