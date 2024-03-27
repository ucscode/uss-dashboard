<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Foundation\User\Controller\Abstract\AbstractProfileController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends AbstractProfileController
{    
    public function onload(ParameterBag $container): Response
    {
        parent::onload($context);
        $this->form->build();
        $this->form->handleSubmission();
    }
}
