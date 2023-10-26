<?php

class AdminUserController implements RouteInterface 
{
    public function __construct(
        protected Archive $archive,
        protected DashboardInterface $dashboard
    ){  
    }

    public function onload(array $matches)
    {
        $this->createCrud();
        $this->archive->getMenuItem('users', true)?->setAttr('active', true);
        $template = $this->archive->getTemplate();
        $this->dashboard->render($template);
    }  

    protected function createCrud(): CrudManager
    {
        $crudManager = new CrudManager(User::USER_TABLE);
        return $crudManager;
    }
}