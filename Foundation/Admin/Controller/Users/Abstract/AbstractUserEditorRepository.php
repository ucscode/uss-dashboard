<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Abstract;

use Uss\Component\Common\AppStore;
use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Form\Form;
use Ucscode\UssForm\Gadget\Gadget;
use Ucscode\UssForm\Resource\Facade\Position;
use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Bundle\User\Interface\UserConstInterface;

abstract class AbstractUserEditorRepository extends AbstractUsersController
{
    protected CrudEditor $crudEditor;
    protected CrudEditorForm $form;
    protected User $client;

    public function __construct(array $context)
    {
        parent::__construct($context);
        $this->updateProperties();
        $this->configureGUI();
    }

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

    protected function updateProperties(): void
    {
        $this->crudEditor = new CrudEditor(UserConstInterface::TABLE_USER);
        $this->form = $this->crudEditor->getForm();
        $this->client = new User();
        $this->form->attribute->setEnctype("multipart/form-data");
    }

    protected function configureGUI(): void
    {
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

    protected function removeSensitiveFields(): void
    {
        $needless = [
            'id',
            'last_seen',
            'usercode',
        ];

        foreach($needless as $key) {
            $this->form
                ->getCollection(Form::DEFAULT_COLLECTION)
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

        // Configure Password Field;

        $required = $this->crudEditor->getChannel() === CrudEnum::CREATE;
        $info = $required ? null : 'Leave blank to preserve current password';

        $this->crudEditor
            ->configureField('password', [
                'required' => $required,
                'value' => '',
                'info' => $this->info($info),
            ]);
        
        $this->crudEditor
            ->configureField('register_time', [
                'nodeType' => Field::TYPE_DATETIME_LOCAL,
            ]);
        
        $parentEmail = $this->client->getParent(true)?->getEmail();
        $info = $parentEmail ? sprintf('Current parent is %s', $parentEmail) : null;

        $this->crudEditor
            ->configureField('parent', [
                'required' => false,
                'attributes' => [
                    'placeholder' => 'Parent Referral Code',
                ],
                'info' => $this->info($info),
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

        $permissions = AppStore::instance()->get('dashboard:permissions');
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
        if($this->crudEditor->getChannel() === CrudEnum::UPDATE) {
            $this->crudEditor->setEntityPropertiesByOffset($_GET['entity'] ?? '');
            if($this->crudEditor->hasEntityProperties()) {
                $entity = $this->crudEditor->getEntity();
                $this->client = new User($entity->get('id'));
            }
        }
        $this->crudEditor->getForm()->populate([
            'password' => null,
            'parent' => null,
        ]);
    }

    private function info(?string $info): ?string
    {
        if(!is_null($info)) {
            return sprintf(
                '<span class="text-muted small">
                    <i class="bi bi-info-circle"></i> 
                    %s
                </span>',
                $info
            );
        }
        return $info;
    }
}