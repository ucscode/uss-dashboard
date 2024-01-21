<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Abstract;

use Ucscode\UssForm\Field\Field;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Ucscode\UssForm\Form\Form;

abstract class AbstractCreateController extends AbstractUsersController
{
    protected CrudEditor $crudEditor;
    protected Form $form;

    protected function removeSensitiveFields(): void
    {
        $needless = [
            'id',
            'last_seen',
            'usercode',
        ];

        foreach($needless as $key) {
            $this->form->getCollection(Form::DEFAULT_COLLECTION)
                ->removeField($key);
        }
    }

    protected function configureCrudEditor(): void
    {
        $this->crudEditor
            ->configureField('email', [
                'nodeType' => Field::TYPE_EMAIL,
            ]);

        $this->crudEditor
            ->configureField('username', [
                'required' => false,
            ]);
        
        $this->crudEditor
            ->configureField('register_time', [
                'nodeType' => Field::TYPE_DATETIME_LOCAL,
            ]);
        
        $this->crudEditor
            ->configureField('parent', [
                'attributes' => [
                    'placeholder' => 'Parent Referral Code',
                ]
            ]);
    }
}