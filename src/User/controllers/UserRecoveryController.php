<?php

class UserRecoveryController implements RouteInterface
{
    public function __construct(
        private Archive $archive,
        private DashboardInterface $dashboard
    ) {
    }

    public function onload($pageInfo)
    {
        $formName = $this->archive->name;
        $formClass = $this->archive->getForm();

        $formInstance = new $formClass($formName);
        $formInstance->handleSubmission();

        $this->dashboard->enableFirewall(false);
        $this->dashboard->render($this->archive->getTemplate(), [
            'form' => $formInstance
        ]);
    }
}
