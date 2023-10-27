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
        $this->archive->getMenuItem('users', true)?->setAttr('active', true);
        $template = $this->archive->getTemplate();

        $crudIndexManager = new CrudIndexManager(User::USER_TABLE);
        //$crudIndexManager->setDisplayItemActionsAsButton(true);

        $this->dashboard->render($template, [
            'crudIndex' => $crudIndexManager->createUI()
        ]);
    }
}
