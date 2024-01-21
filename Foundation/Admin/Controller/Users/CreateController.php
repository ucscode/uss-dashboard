<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FormManager;
use Module\Dashboard\Foundation\Admin\Controller\Users\Abstract\AbstractFieldConstructor;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Resource\Facade\Position;

class CreateController extends AbstractFieldConstructor
{
    protected function composeMicroApplication(): void
    {
        $this->enableDocumentMenu('main:users.create');
        parent::composeMicroApplication();

        $this->generateField([
            'nodeType' => Field::TYPE_CHECKBOX,
            'position' => Position::BEFORE,
            'position-target' => FormManager::SUBMIT_KEY,
            'name' => 'notify-user',
            'label' => 'Send email to user after registration',
        ]);
    }
}