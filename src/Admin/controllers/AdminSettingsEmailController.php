<?php

class AdminSettingsEmailController implements RouteInterface
{
    public function __construct(
        protected PageManager $pageManager,
        protected DashboardInterface $dashboard
    ){}

    public function onload(array $matches)
    {
        $this->pageManager->getMenuItem(AdminDashboardInterface::PAGE_SETTINGS_EMAIL, true)
            ?->setAttr('active', true);
            
        $form = $this->pageManager->getForm();

        $this->dashboard->render($this->pageManager->getTemplate(), [
            'form' => $form
        ]);
    }
}