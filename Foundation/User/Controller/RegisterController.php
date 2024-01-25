<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;

class RegisterController extends AbstractDashboardController
{
    public function onload(array $context): void
    {
        parent::onload($context);
        $this->dashboard->enableFirewall(false);
        $this->form->build();
        $this->form->handleSubmission();
        $this->dashboard->render($this->document->getTemplate(), [
            'form' => $this->form
        ]);
    }

}
