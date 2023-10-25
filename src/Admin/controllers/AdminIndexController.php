<?php

class AdminIndexController implements RouteInterface
{
    public function __construct(
        private Archive $archive,
        private DashboardInterface $dashboard
    ) {

    }
    public function onload(array $matches)
    {
        $this->dashboard->render($this->archive->getTemplate());
    }

}
