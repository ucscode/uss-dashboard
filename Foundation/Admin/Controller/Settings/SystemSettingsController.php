<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Settings;

use Exception;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;

class SystemSettingsController extends AbstractDashboardController
{
    public function onload(array $context): void
    {
        parent::onload($context);

        $this->form->handleSubmission()
        ->catch(function(Exception $e) {
            $modal = (new Modal())
                ->setMessage(str_replace("\n", "<br>", $e->getMessage()));
            Flash::instance()->addModal($modal);
        });

        $this->form->build();
    }
}