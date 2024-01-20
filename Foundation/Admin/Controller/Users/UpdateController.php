<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

class UpdateController extends CreateController
{
    protected function composeMicroApplication(): void
    {
        parent::composeMicroApplication();
        $this->enableDocumentMenu('main:users');
        $this->crudEditor->setEntityByOffset($_GET['entity'] ?? '');
    }
}