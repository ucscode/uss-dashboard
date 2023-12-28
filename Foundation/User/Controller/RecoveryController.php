<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Route\RouteInterface;

class RecoveryController implements RouteInterface
{
    public function onload($pageInfo)
    {
        $dashboard = UserDashboard::instance()->enableFirewall(false);
        $document = $dashboard->getDocument('recovery');
        $formInstance = $document->getForm();
        $formInstance->handleSubmission();
        $dashboard->render($document->getTemplate(), [
            'form' => $formInstance
        ]);
    }
}
