<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Settings;

use Module\Dashboard\Foundation\Admin\Controller\Settings\Abstract\AbstractSettingsController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class UsersSettingsController extends AbstractSettingsController
{
    public function onload(ParameterBag $container): Response
    {
        parent::onload($context);
        $this->form->handleSubmission();
        $this->form->build();
    }
}