<?php

class UserRegisterController implements RouteInterface
{
    public function __construct(
        private PageManager $pageManager,
        private DashboardInterface $dashboard
    ) {

    }

    public function onload($regex)
    {
        $formInstance = $this->pageManager->getForm();
        $formInstance->handleSubmission();

        $this->dashboard->enableFirewall(false);

        $this->dashboard->render($this->pageManager->getTemplate(), [
            'form' => $formInstance
        ]);
    }

}
