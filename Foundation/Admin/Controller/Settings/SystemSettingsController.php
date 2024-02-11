<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Settings;

use Exception;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Foundation\Admin\Controller\Settings\Abstract\AbstractSettingsController;

class SystemSettingsController extends AbstractSettingsController
{
    public function onload(array $context): void
    {
        parent::onload($context);

        try {
            $this->form->handleSubmission();
        }catch(Exception $e) {
            $modal = (new Modal())
                ->setMessage(str_replace("\n", "<br>", $e->getMessage()));
            Flash::instance()->addModal($modal);
        };

        $this->form->build();
    }
}