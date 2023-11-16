<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

class AdminUserController implements RouteInterface
{
    protected array $userRoles = [
        RoleImmutable::ROLE_ADMIN,
        RoleImmutable::ROLE_USER
    ];

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
        $this->processCrudManagers($template);
    }

    /**
     * @method processCrudManagers
     */
    protected function processCrudManagers(string $template): void
    {
        //(new FakeUser())->create(100);
        $crudProcessAutomator = new CrudProcessAutomator(User::USER_TABLE);
        $crudProcessAutomator->processAllActions();

        $this->configureIndexManager($crudProcessAutomator->getCrudIndexManager());
        $this->configureEditManager($crudProcessAutomator->getCrudEditManager());

        $automatorUI = $crudProcessAutomator->getCreatedUI();
        
        $this->dashboard->render($template, [
            'crudIndex' => $automatorUI->getHTML(true)
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
        $crudEditManager->removeField('id');

        $emailField = new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_EMAIL);
        $crudEditManager->setField('email', $emailField);

        $crudEditManager
            ->getField('username')
                ->setWidgetAttribute('pattern', '^[a-z0-9_\\-]+$')
                ->setRequired(false);

        $crudEditManager
            ->getField('password')
                ->setWidgetAttribute('placeholder', str_repeat('*', 6));

        $this->addExtraFields($crudEditManager);

        $item = $crudEditManager->getItem();

        switch($crudEditManager->getCurrentAction()) {

            case CrudActionImmutableInterface::ACTION_READ:

                $crudEditManager->removeField('password');

                if(!empty($item)) {
                    if(!empty($item['parent'])) {
                        $user = new User($item['parent']);
                        $item['parent'] = $user->getEmail();
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

                } else {



                }

                break;

        }

        $crudEditManager->setItem($item);

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
                ) {
                }

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
                    $this->roles = $data['role'] ?? [];
                    unset($data['role']);
                    return $data;
                }

                /**
                 * @override
                 */
                public function afterEntry(bool $status, array $item): bool
                {
                    $user = new User($item['id']);
                    $user->setRoles($this->roles);
                    return true;
                }
            }
        );
    }

    /**
     * @method addExtraEditFields
     */
    protected function addExtraFields(CrudEditManager $crudEditManager): void
    {
        // $fieldContainer = new FieldContainer()
        //     ->setTitle()
        //     ->setClass()
        //     ->isFieldset()
        //     ->setCaption()
        //     ->setLegend()
        //     ->setDisabled();

        foreach($this->userRoles as $key => $value) {

            $roleField = new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_CHECKBOX);
            $roleField
                ->setWidgetValue($value)
                ->setWidgetAttribute('name', 'role[]')
                ->setContainerAttribute('class', 'mb-1')
                ->setRequired(false)
            ;

            $crudEditManager->setField("_role_{$key}", $roleField);
        }
    }
}
