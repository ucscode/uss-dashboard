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
        $registerForm = $this->pageManager->getForm();
        $formInstance = new $registerForm($this->pageManager->name);
        $formInstance->handleSubmission();

        $this->dashboard->enableFirewall(false);

        $this->dashboard->render($this->pageManager->getTemplate(), [
            'form' => $formInstance
        ]);
    }

}
