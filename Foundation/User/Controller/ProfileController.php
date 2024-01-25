<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Foundation\User\Controller\Abstract\AbstractProfileController;

class ProfileController extends AbstractProfileController
{    
    public function onload(array $context): void
    {
        parent::onload($context);
        $this->form->build();
        $this->form->handleSubmission();
    }
}
