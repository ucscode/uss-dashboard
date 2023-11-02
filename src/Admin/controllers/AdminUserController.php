<?php

use Ucscode\DOMTable\DOMTableInterface;

class AdminUserController implements RouteInterface
{
    public function __construct(
        protected Archive $archive,
        protected DashboardInterface $dashboard
    ) {
    }

    public function onload(array $matches)
    {
        $this->archive->getMenuItem('users', true)?->setAttr('active', true);
        $template = $this->archive->getTemplate();

        $crudProcessAutomator = new CrudProcessAutomator(User::USER_TABLE);
        //(new FakeUser())->create(20);
        $crudProcessAutomator->processAllActions();
        $this->configureIndexManager($crudProcessAutomator->getCrudIndexManager());
        $this->configureEditManager($crudProcessAutomator->getCrudEditManager());
        $automatorUI = $crudProcessAutomator->getCreatedUI();

        $editable = [
            CrudActionImmutableInterface::ACTION_CREATE,
            CrudActionImmutableInterface::ACTION_UPDATE,
            CrudActionImmutableInterface::ACTION_DELETE
        ];

        if(!in_array(($_GET['action'] ?? null), $editable)) {

            $crudIndexManager = new CrudIndexManager(User::USER_TABLE);



        } else {

            $crudEditManager = new CrudEditManager(User::USER_TABLE);
            $crudEditManager->setItemBy('id', $_GET['entity'] ?? null);

            $crudEditManager->removeField('id');

            $crudField = (new CrudField())
                ->setLabel('worker')
                ->setType(CrudField::TYPE_BOOLEAN)
                ->setAttribute('name', 'worked')
            ;

            $crudEditManager->setField('user[changer]', $crudField);
            $crudEditManager->getField('email')->setType(CrudField::TYPE_EMAIL);

            $ui = $crudEditManager->createUI(new class () implements CrudEditSubmitInterface {
                private array $data;
                public function beforeEntry(array $data): array
                {
                    $this->data = $data;
                    unset($data['worked']);
                    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                    $data['usercode'] = Uss::instance()->keygen(7);
                    return $data;
                }
                public function afterEntry(bool $status, array $data): bool
                {
                    return true;
                }
            });

        }

        $this->dashboard->render($template, [
            'crudIndex' => $automatorUI->getHTML(true)
        ]);
    }

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

    protected function configureEditManager(CrudEditManager $crudEditManager): void
    {
        $crudEditManager->removeField('id');
        $crudEditManager->removeField('password');
        $item = $crudEditManager->getItem();
        if(!empty($item['parent'])) {
            $user = new User($item['parent']);
            $item['parent'] = $user->getEmail();
        };
        if(empty($item['parent'])) {
            $item['parent'] = '<span class="text-muted">NULL</span>';
        }
        $crudEditManager->setItem($item);
    }
}
