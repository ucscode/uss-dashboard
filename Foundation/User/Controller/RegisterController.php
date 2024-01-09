<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;

class RegisterController extends AbstractDashboardController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $dashboard->enableFirewall(false);
        $form->build();
        $form->handleSubmission();
        $dashboard->render($document->getTemplate(), ['form' => $form]);
    }

}
