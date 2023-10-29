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

        if(($_GET['action'] ?? null) !== 'edit') {

            $crudIndexManager = new CrudIndexManager(User::USER_TABLE);
            $crudIndexManager->removeTableColumn('id');
            $crudIndexManager->removeTableColumn('password');
            $crudIndexManager->setTableColumn('model', 'Model No.');
            $crudIndexManager->setDisplayItemActionsAsButton(true);
            //$crudIndexManager->setHideWidgets(true);
            $crudIndexManager->setItemsPerPage(2);

            $ui = $crudIndexManager->createUI(new class () implements DOMTableInterface {
                public function forEachItem(array $data): array
                {
                    $data['model'] = 'sample ' . $data['id'];
                    return $data;
                }
            });

        } else {

            $crudEditManager = new CrudEditManager(User::USER_TABLE);

            $crudEditManager->removeField('id');

            $crudField = (new CrudField())
                ->setLabel('worker')
                ->setType(CrudField::TYPE_BOOLEAN)
                ->setAttribute('name', 'worked')
                ->setMapped(false)
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
            'crudIndex' => $ui->getHTML(true)
        ]);
    }
}
