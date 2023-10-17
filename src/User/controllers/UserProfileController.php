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
        $this->archive->getMenuItem('profilePill', true)?->setAttr('active', true);
        $this->dashboard->render($this->archive->get('template'));
    }
}
