<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractFieldConstructor;

class UpdateController extends AbstractFieldConstructor
{
    protected function composeMicroApplication(): void
    {
        parent::composeMicroApplication();
        $this->enableDocumentMenu('main:users');
        $this->crudEditor->setEntityByOffset($_GET['entity'] ?? '');
    }
}