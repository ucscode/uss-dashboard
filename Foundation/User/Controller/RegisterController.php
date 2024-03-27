<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends AbstractDashboardController
{
    public function onload(ParameterBag $container): Response
    {
        parent::initialize($container);

        $this->dashboard->enableFirewall(false);
        $this->form->build();
        $this->form->handleSubmission();

        return $this->dashboard->render($this->document->getTemplate(), [
            'form' => $this->form
        ]);
    }

}
