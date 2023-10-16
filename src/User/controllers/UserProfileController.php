<?php

class UserProfileController implements RouteInterface
{
    public function __construct(
        protected Archive $archive,
        protected DashboardInterface $dashboard
    ) {

    }

    public function onload(array $matches)
    {
        $this->dashboard->render($this->archive->get('template'));
    }
}
