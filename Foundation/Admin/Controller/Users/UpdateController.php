<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractUserEditorRepository;
use Module\Dashboard\Foundation\Admin\Controller\Users\Process\OnUpdateSubmit;
use Module\Dashboard\Foundation\Admin\Controller\Users\Tool\UserControl;

class UpdateController extends AbstractUserEditorRepository
{
    public function __construct(array $context)
    {
        parent::__construct($context);
        $this->enableDocumentMenu('main:users');
        $this->processUpdateRequest();
    }

    protected function processUpdateRequest(): void
    {
        $submitAction = new OnUpdateSubmit($this->client, $this->crudEditor, $this->dashboard);
        
        $this->form
            ->addSubmitAction('user:update', $submitAction)
            ->handleSubmission();

        if($this->form->isSubmitted() && $this->form->isPersisted()) {
            $this->initializeClient();
            $roles = $this->client->roles->getAll();
            (new UserControl($this->crudEditor))->autoCheckRolesCheckbox($roles);
        }
    }
}