<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

class AdminUserController implements RouteInterface
{
    protected array $userRoles = [
        RoleImmutable::ROLE_SUPERADMIN,
        RoleImmutable::ROLE_ADMIN,
        RoleImmutable::ROLE_USER,
        RoleImmutable::ROLE_CUSTOMER,
    ];

    protected ?User $user = null;

    /**
     * @method __construct
     */
    public function __construct(
        protected Archive $archive,
        protected DashboardInterface $dashboard
    ) {
    }

    /**
     * @override
     */
    public function onload(array $matches)
    {
        $this->archive->getMenuItem('users', true)?->setAttr('active', true);
        $template = $this->archive->getTemplate();
        $entityUI = $this->processCrudManagers();
        $this->renderUserInterface($template, $entityUI);
    }

    /**
     * @method processCrudManagers
     */
    protected function processCrudManagers(): UssElement
    {
        //(new FakeUser())->create(100);
        $crudProcessAutomator = new CrudProcessAutomator(User::USER_TABLE);
        $crudProcessAutomator->processAllActions();

        $this->configureIndexManager($crudProcessAutomator->getCrudIndexManager());
        $this->configureEditManager($crudProcessAutomator->getCrudEditManager());

        return $crudProcessAutomator->getCreatedUI();
    }

    /**
     * @method renderUserInterface
     */
    protected function renderUserInterface(string $template, UssElement $entityUI): void
    {
        $this->dashboard->render($template, [
            'crudContent' => $entityUI->getHTML(true)
        ]);
    }

    /**
     * @method configureIndexManager
     */
    protected function configureIndexManager(CrudIndexManager $crudIndexManager): void
    {
        $crudIndexManager->removeTableColumn('id');
        $crudIndexManager->removeTableColumn('password');
        $crudIndexManager->removeTableColumn('last_seen');
        $crudIndexManager->removeTableColumn('parent');
        $crudIndexManager->setTableColumn('role', 'Role');
        $crudIndexManager->setTableColumn('register_time', 'Registered');
        $crudIndexManager->setDisplayItemActionsAsButton(true);
        $crudIndexManager->setItemsPerPage(15);
        $crudIndexManager->setTableWhiteBackground();
        //$crudIndexManager->setHideWidgets(true);
        //$crudIndexManager->setDisplayTableFooter(true);
        //$crudIndexManager->setHideBulkActions(true);
        //$crudIndexManager->setHideItemActions(true);

        /*
            $crudIndexManager->manageBulkActionSubmission(new class () implements CrudBulkActionsInterface {
                public function onSubmit(string $action, array $selections): void
                {
                    var_dump($action, $selections);
                }
            });
        */

        $crudIndexManager->setModifier(
            new class () implements DOMTableInterface {
                public function forEachItem(array $item): array
                {
                    $item = $this->modifyRole($item);
                    $item['register_time'] = (new \DateTime($item['register_time']))->format('d-M-Y');
                    return $item;
                }

                protected function modifyRole(array $item): array
                {
                    $user = new User($item['id']);
                    $count = count($user->getRoles());
                    if($count > 1) {
                        $item['role'] = "<span class='text-primary'>" . $count . " Roles</span>";
                    } elseif($count < 1) {
                        $item['role'] = '<span class="text-danger">None</span>';
                    } else {
                        $item['role'] = $user->getRoles(0);
                    }
                    return $item;
                }
            }
        );
    }

    /**
     * @method configureEditManager
     */
    protected function configureEditManager(CrudEditManager $crudEditManager): void
    {
        $item = $crudEditManager->getItem();

        // Get associated user
        $this->user = new User($item ? ($item['id'] ?? -1) : null);

        // Change the column size of the default fieldstack
        $crudEditManager->getEditForm()->getFieldStack('default')
            ->setOuterContainerAttribute('class', 'col-lg-8', true);

        // Modify Fields
        $crudEditManager->removeField('id');
        
        $prevEmailField = $crudEditManager->getField('email');

        $emailField = (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_EMAIL))
            ->setRowAttribute('class', $prevEmailField->getRowAttribute('class'), true);
        
        $crudEditManager->setField('email', $emailField);

        $crudEditManager
            ->getField('username')
                ->setWidgetAttribute('pattern', '^[a-z0-9_\\-]+$')
                ->setRequired(false);

        $crudEditManager
            ->getField('password')
                ->setWidgetAttribute('placeholder', str_repeat('*', 6));

        $crudEditManager
            ->getField('register_time')
            ->addLineBreak();

        // Set default register time
        if($crudEditManager->getCurrentAction() === CrudActionImmutableInterface::ACTION_CREATE) {
            $time = (new DateTime('now'))->format('Y-m-d H:i:s');
            $crudEditManager->getField('register_time')->setWidgetValue($time);
        };

        // Add available roles
        $this->addRoleFields($crudEditManager);

        // Configure Visuals based on current Actions
        switch($crudEditManager->getCurrentAction()) {

            case CrudActionImmutableInterface::ACTION_READ:

                $crudEditManager->removeField('password');

                if(!empty($item)) {
                    if(!empty($item['parent'])) {
                        $parentUser = new User($item['parent']);
                        $item['parent'] = $parentUser->getEmail();
                    };
                    if(empty($item['parent'])) {
                        $item['parent'] = '<span class="text-muted">NULL</span>';
                    }
                }

                break;

            case CrudActionImmutableInterface::ACTION_CREATE:
            case CrudActionImmutableInterface::ACTION_UPDATE:

                $crudEditManager->removeField('last_seen');
                $crudEditManager->removeField('usercode');

                $crudEditManager
                    ->getField('parent')
                        ->setRequired(false);

                if($crudEditManager->getCurrentAction() === CrudActionImmutableInterface::ACTION_UPDATE) {

                    $crudEditManager
                        ->getField('password')
                            ->setRequired(false);

                    $item['password'] = null;

                }

                break;

        }

        // Update the items
        $crudEditManager->setItem($item);

        // Handle Submit Events
        $crudEditManager->setModifier(
            //
            new class ($crudEditManager) implements CrudEditSubmitInterface {
                /**
                 * @var array $roles
                 */
                protected array $roles;

                /**
                 * @method __construct
                 */
                public function __construct(
                    protected CrudEditManager $crudEditManager
                ) {}

                /**
                 * @override
                 */
                public function beforeEntry(array $data): array
                {
                    $data['username'] = $data['username'] ?: null;
                    if(empty($data['password']) && $this->crudEditManager->getCurrentAction() === CrudActionImmutableInterface::ACTION_UPDATE) {
                        unset($data['password']);
                    } else {
                        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                    }
                    $data['parent'] = $data['parent'] ?: null;
                    $data['usercode'] = Uss::instance()->keygen(7);
                    $this->roles = $data['roles'] ?? [];
                    unset($data['roles']);
                    return $data;
                }

                /**
                 * @override
                 */
                public function afterEntry(bool $status, array $item): bool
                {
                    // if registration was successful
                    if($status) {
                        $user = new User($item['id']);
                        $user->setRoles($this->roles);
                    }
                    return true;
                }
            }
        );
    }

    /**
     * @method addExtraEditFields
     */
    protected function addRoleFields(CrudEditManager $crudEditManager): void
    {
        $fieldstack = $crudEditManager->getEditForm()->addFieldStack('roles', true);
        $fieldstack
            ->removeOuterContainerAttribute('class', 'col-12', true)
            ->setOuterContainerAttribute('class', 'col-lg-4', true);

        $roleField = new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_CHECKBOX);
        $roleField
            ->setValidationHidden(true)
            ->setContainerAttribute('class', 'border p-3 rounded my-2', true)
            ->setInfoMessage("Select all roles for the user")
            ->setInfoAttribute('class', 'mb-2 alert alert-info', true)
            ->setLabelValue($this->userRoles[0])
            ->setWidgetValue($this->userRoles[0])
            ->setRequired(false)
            ->setWidgetChecked($this->user && $this->user->hasRole($this->userRoles[0]));
        
        $crudEditManager->setField('roles[]', $roleField);

        foreach($this->userRoles as $key => $role) {
            if($key) {
                $fieldName = strtolower('role_' . $role);
                $secondaryField = $roleField->createSecondaryField($fieldName, UssForm::TYPE_CHECKBOX);
                $secondaryField
                    ->setLabelValue($role)
                    ->setRequired(false)
                    ->setWidgetValue($role)
                    ;
                if($this->user && $this->user->hasRole($role)) {
                    $secondaryField->setWidgetChecked(true);
                }
            }
        }
    }
}
