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

        $crudIndexManager = new CrudIndexManager(User::USER_TABLE);
        $crudIndexManager->removeTableColumn('id');
        $crudIndexManager->removeTableColumn('password');
        $crudIndexManager->setTableColumn('model', 'Model No.');
        $crudIndexManager->setDisplayItemActionsAsButton(true);

        $ui = $crudIndexManager->createUI(new class implements DOMTableInterface {
            public function forEachItem(array $data): array
            {
                $data['model'] = 'sample ' . $data['id'];
                return $data;
            }
        });

        $this->dashboard->render($template, [
            'crudIndex' => $ui->getHTML(true)
        ]);
    }
}
