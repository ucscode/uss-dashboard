<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Settings;

use Module\Dashboard\Foundation\Admin\Controller\Settings\Abstract\AbstractSettingsController;

class UsersSettingsController extends AbstractSettingsController
{
    public function onload(array $context): void
    {
        parent::onload($context);
        $this->form->handleSubmission();
        $this->form->build();
    }
}