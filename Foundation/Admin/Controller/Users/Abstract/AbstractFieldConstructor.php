<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Abstract;

use Module\Dashboard\Bundle\Common\AppStore;
use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Ucscode\UssForm\Field\Field;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Form\Form;
use Ucscode\UssForm\Gadget\Gadget;
use Ucscode\UssForm\Resource\Facade\Position;
use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use Module\Dashboard\Bundle\User\User;

abstract class AbstractFieldConstructor extends AbstractUsersController
{
    protected CrudEditor $crudEditor;
    protected CrudEditorForm $form;
    protected User $client;

    public function getCrudKernel(): CrudKernelInterface
    {
        return $this->crudEditor;
    }

    public function getForm(): ?Form
    {
        return $this->crudEditor->getForm();
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    protected function composeMicroApplication(): void
    {
        $this->crudEditor = new CrudEditor(UserInterface::USER_TABLE);
        $this->form = $this->crudEditor->getForm();
        $this->form->attribute->setEnctype("multipart/form-data");
        $this->client = new User();

        $this->initializeClient();
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

    protected function iterateRolesGadget(callable $callback, $filter = false): void
    {
        $roleField = $this->form->getCollection('roles')->getField('roles');
        $callback($roleField->getElementContext()->gadget);
        foreach($roleField->getGadgets() as $gadget) {
            if($filter && !$gadget->widget->hasAttribute('data-role')) {
                continue;
            }
            $callback($gadget);
        }
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
                'value' => '',
                'info' => '<span class="text-muted small">
                    <i class="bi bi-info-circle"></i> 
                    Leave blank to preseve current password
                </span>',
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
            ->setAttribute('data-ui-preview-uploaded-image-in', '#avatar-image')
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
        $clientRoles = $this->client->roles->getAll();
        
        [$gadget, $input] = $this->configureRoleGadget(
            $field, 
            $field->getElementContext()->gadget, 
            $role,
            $clientRoles
        );

        $field->addGadget($role . 'input', $input);
        $field->setGadgetPosition($input, Position::BEFORE, $gadget);

        foreach($permissions as $role) {
            $gadget = new Gadget(Field::NODE_INPUT, Field::TYPE_CHECKBOX);

            [$gadget, $input] = $this->configureRoleGadget($field, $gadget, $role, $clientRoles);
            
            $field->addGadget($role . 'input', $input);
            $field->addGadget($role, $gadget);
        }

        $rolesCollection->addField('roles', $field);
    }

    protected function configureRoleGadget(Field $field, Gadget $gadget, string $role, array $clientRoles): array
    {
        $name = sprintf('roles[%s]', $role);
        $gadget->widget
            ->setRequired(false)
            ->setChecked(in_array($role, $clientRoles))
            ->setValue(1)
            ->setAttribute('name', $name)
            ->setAttribute('data-role', $role)
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

    protected function initializeClient(): void
    {
        if(($_GET['channel'] ?? null) === CrudEnum::UPDATE->value) {
            $this->crudEditor->setEntityByOffset($_GET['entity'] ?? '');
            if($this->crudEditor->hasEntity()) {
                $this->crudEditor->getForm()->populate([
                    'password' => null
                ]);
                $entity = $this->crudEditor->getEntity();
                $this->client = new User($entity['id']);
            }
        }
    }
}