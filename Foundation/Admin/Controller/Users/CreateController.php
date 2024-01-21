<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractCreateController;
use Ucscode\UssForm\Form\Form;

class CreateController extends AbstractCreateController
{
    protected function composeMicroApplication(): void
    {
        $this->enableDocumentMenu('main:users.create');
        $this->crudEditor = new CrudEditor(UserInterface::USER_TABLE);
        $this->form = $this->crudEditor->getForm();
        $this->removeSensitiveFields();
        $this->configureCrudEditor();
    }

    public function getCrudKernel(): CrudKernelInterface
    {
        return $this->crudEditor;
    }

    public function getForm(): ?Form
    {
        return $this->crudEditor->getForm();
    }
}