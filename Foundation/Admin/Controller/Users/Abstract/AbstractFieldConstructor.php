<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Abstract;

use Module\Dashboard\Bundle\Common\AppStore;
use Ucscode\UssForm\Field\Field;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Form\Form;
use Ucscode\UssForm\Gadget\Gadget;
use Ucscode\UssForm\Resource\Facade\Position;
use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;

abstract class AbstractFieldConstructor extends AbstractUsersController
{
    protected CrudEditor $crudEditor;
    protected Form $form;

    public function getCrudKernel(): CrudKernelInterface
    {
        return $this->crudEditor;
    }

    public function getForm(): ?Form
    {
        return $this->crudEditor->getForm();
    }

    protected function composeMicroApplication(): void
    {
        $this->crudEditor = new CrudEditor(UserInterface::USER_TABLE);
        $this->form = $this->crudEditor->getForm();
        $this->form->attribute->setEnctype("multipart/form-data");
        $this->removeSensitiveFields();
        $this->configurePrimaryFields();
        $this->createAvatarCollections();
        $this->createRolesCollection();
    }

    protected function generateField(array $info): Field
    {
        $field = new Field(
            $info['nodeName'] ?? Field::NODE_INPUT, 
            $info['nodeType'] ?? Field::TYPE_TEXT
        );

        $context = $field->getElementContext();
        $context->label->setValue($info['label'] ?? null);

        $collection = $this->form->getCollection($info['collection-target'] ?? Form::DEFAULT_COLLECTION);
        $collection->addField($info['name'] ?? rand(), $field);
        
        if($info['position'] instanceof Position && !empty($info['position-target'])) {
            $collection->setFieldPosition($field, $info['position'], $info['position-target']);
        }

        return $field;
    }

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

    protected function configurePrimaryFields(): void
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
            ->configureField('password', [
                'required' => false,
            ]);
        
        $this->crudEditor
            ->configureField('register_time', [
                'nodeType' => Field::TYPE_DATETIME_LOCAL,
            ]);
        
        $this->crudEditor
            ->configureField('parent', [
                'required' => false,
                'attributes' => [
                    'placeholder' => 'Parent Referral Code',
                ],
            ]);
    }

    protected function createAvatarCollections(): void
    {
        $avatarCollection = new Collection();
        $this->form->addCollection('avatar', $avatarCollection);
        $field = new Field(Field::NODE_INPUT, Field::TYPE_FILE);
        $avatarCollection->addField('avatar', $field);

        $gadget = $field->getElementContext()->gadget;
        $gadget->container->addClass('d-none');
        $gadget->widget
            ->setRequired(false)
            ->setAttribute('id', 'avatar-input')
            ->setAttribute('accept', 'jpg,png,gif,jpeg,webp')
        ;
    }

    protected function createRolesCollection(): void
    {
        $rolesCollection = new Collection();
        $this->form->addCollection('roles', $rolesCollection);
        $field = new Field(Field::NODE_INPUT, Field::TYPE_CHECKBOX);

        $permissions = AppStore::instance()->get('app:permissions');
        sort($permissions);
        $role = array_shift($permissions);
        
        [$gadget, $input] = $this->configureRoleGadget($field, $field->getElementContext()->gadget, $role);

        $field->addGadget($role . 'input', $input);
        $field->setGadgetPosition($input, Position::BEFORE, $gadget);

        foreach($permissions as $role) {
            $gadget = new Gadget(Field::NODE_INPUT, Field::TYPE_CHECKBOX);

            [$gadget, $input] = $this->configureRoleGadget($field, $gadget, $role);
            
            $field->addGadget($role . 'input', $input);
            $field->addGadget($role, $gadget);
        }

        $rolesCollection->addField('roles', $field);
    }

    protected function configureRoleGadget(Field $field, Gadget $gadget, string $role): array
    {
        $name = sprintf('roles[%s]', $role);
        $gadget->widget
            ->setRequired(false)
            ->setValue(1)
            ->setAttribute('name', $name)
        ;
        $gadget->label->setValue($role);
        $gadget->container
            ->addClass('form-check-inline')
        ;

        $inputGadget = new Gadget(Field::NODE_INPUT, Field::TYPE_HIDDEN);
        $inputGadget->widget
            ->setRequired(false)
            ->setAttribute('name', $name)
            ->setValue(0)
        ;
        $inputGadget->container
            ->addClass('d-none')
        ;
        return [$gadget, $inputGadget];
    }
}