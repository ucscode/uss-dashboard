<?php

class AdminSettingsDefaultController implements RouteInterface
{
    public function __construct(
        protected PageManager $pageManager,
        protected DashboardInterface $dashboard
    ){}
    
    public function onload(array $matches)
    {
        $template = $this->pageManager->getTemplate();

        $this
            ->pageManager
            ->getMenuItem(AdminDashboardInterface::PAGE_SETTINGS_DEFAULT, true)
            ?->setAttr('active', true);

        $form = $this->pageManager->getForm();
        $form = new $form($this->pageManager->name);

        $this->dashboard->render($template, [
            'form' => $form
        ]);
    }
}