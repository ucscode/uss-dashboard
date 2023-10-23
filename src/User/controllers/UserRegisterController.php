<?php

class UserRegisterController implements RouteInterface
{
    public function __construct(
        private Archive $archive,
        private DashboardInterface $dashboard
    ) {

    }

    public function onload($regex)
    {
        $registerForm = $this->archive->getForm();
        $formInstance = new $registerForm($this->archive->name);
        $formInstance->handleSubmission();

        $this->dashboard->enableFirewall(false);

        $this->dashboard->render($this->archive->getTemplate(), [
            'form' => $formInstance
        ]);
    }

}
