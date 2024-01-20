<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Crud\Compact\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;

class UpdateController extends AbstractUsersController
{
    protected CrudEditor $crudEditor;

    protected function composeMicroApplication(): void
    {
        $this->crudEditor = new CrudEditor(UserInterface::USER_TABLE);
        $this->crudEditor->setEntityByOffset($_GET['entity'] ?? '');

        $this->crudEditor
            ->configureField('email', [
                'nodeName' => Field::NODE_SELECT,
                'nodeType' => Field::TYPE_CHECKBOX,
                'options' => [
                    'name' => 'uche',
                    'root@localhost.com' => 'Using Previous Value',
                    'spacelover' => 'I love space'
                ],
                'label' => 'Email Quality',
                'required' => true,
                'disabled' => true,
                'readonly' => true,
            ]);

        $idField = $this->crudEditor
            ->configureField('id', [
                'nodeType' => Field::TYPE_NUMBER
            ])
        ;
        
        $primaryCollection = $this->crudEditor->getForm()->getCollection(Form::DEFAULT_COLLECTION);
        $collection = new Collection();

        $this->crudEditor->getForm()->addCollection("sampler", $collection);
        $this->crudEditor->moveFieldToCollection('email', 'sampler', true);
        $this->crudEditor->moveFieldToCollection($idField, $collection);

        $primaryCollection->getElementContext()->fieldset->addClass('col-lg-6');
        $collection->getElementContext()->fieldset->addClass('col-lg-6');
    }

    public function getComponent(): CrudKernelInterface
    {
        return $this->crudEditor;
    }
}