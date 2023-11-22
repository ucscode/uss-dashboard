<?php

class AdminSettingsController implements RouteInterface
{
    public function __construct(
        protected PageManager $pageManager,
        protected DashboardInterface $dashboard
    ){}

    public function onload(array $matches)
    {
        $this->pageManager->getMenuItem('settings', true)?->setAttr('active', true);
        $template = $this->pageManager->getTemplate();
        $this->dashboard->render($template);
    }
}