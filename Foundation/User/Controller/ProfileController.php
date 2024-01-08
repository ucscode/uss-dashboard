<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Foundation\User\Controller\Abstract\AbstractProfileController;

class ProfileController extends AbstractProfileController
{    
    protected function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        parent::composeApplication($dashboard, $document, $form);
    }
}
