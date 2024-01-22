<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractFieldConstructor;
use Module\Dashboard\Foundation\Admin\Controller\Users\Process\OnUpdateSubmit;

class UpdateController extends AbstractFieldConstructor
{
    protected function composeMicroApplication(): void
    {
        parent::composeMicroApplication();
        $this->enableDocumentMenu('main:users');
        $this->crudEditor->getForm()->addSubmitAction('user-update', new OnUpdateSubmit());
        $this->crudEditor->processSubmitRequest()->then(fn () => $this->initializeClient());
    }
}