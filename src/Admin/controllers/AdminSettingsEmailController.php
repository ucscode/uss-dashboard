<?php

class AdminSettingsEmailController implements RouteInterface
{
    public function __construct(
        protected PageManager $pageManager,
        protected DashboardInterface $dashboard
    ){}

    public function onload(array $matches)
    {
        $form = $this->pageManager->getForm();

        $this->dashboard->render($this->pageManager->getTemplate(), [
            'form' => $form
        ]);
    }
}