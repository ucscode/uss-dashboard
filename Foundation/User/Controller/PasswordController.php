<?php

namespace Module\Dashboard\Foundation\User\Controller;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class PasswordController extends ProfileController
{
    public function onload(ParameterBag $container): Response
    {
        parent::onload($context);
    }
}
