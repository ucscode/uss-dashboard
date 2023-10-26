<?php

class AdminUserController implements RouteInterface
{
    public function __construct(
        protected Archive $archive,
        protected DashboardInterface $dashboard
    ) {
    }

    public function onload(array $matches)
    {
        $crudIndexManager = new CrudIndexManager(User::USER_TABLE);
        $domTable = $crudIndexManager->getDOMTable();
        $domTable->setColumn('last_seen', 'last seen');
        $domTable->setDisplayFooter(true);
        $domTable->build();
        $this->archive->getMenuItem('users', true)?->setAttr('active', true);
        $template = $this->archive->getTemplate();
        $this->dashboard->render($template, [
            'crudIndex' => $domTable->getHTML()
        ]);
    }
}
