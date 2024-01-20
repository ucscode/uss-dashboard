<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

class UpdateController extends CreateController
{
    protected function composeMicroApplication(): void
    {
        parent::composeMicroApplication();
        $this->crudEditor->setEntityByOffset($_GET['entity'] ?? '');
    }
}