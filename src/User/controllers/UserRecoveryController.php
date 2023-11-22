<?php

class UserRecoveryController implements RouteInterface
{
    public function __construct(
        private PageManager $pageManager,
        private DashboardInterface $dashboard
    ) {
    }

    public function onload($pageInfo)
    {
        $formName = $this->pageManager->name;
        $formClass = $this->pageManager->getForm();

        $formInstance = new $formClass($formName);
        $formInstance->handleSubmission();

        $this->dashboard->enableFirewall(false);

        $this->dashboard->render($this->pageManager->getTemplate(), [
            'form' => $formInstance
        ]);
    }
}
