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
        $this->archive->getMenuItem('index', true)?->setAttr('active', true);
        
        $this->dashboard->render($this->archive->getTemplate(), [
            'official_website' => UssImmutable::PROJECT_WEBSITE,
            'title' => UssImmutable::PROJECT_NAME,
            'dev_email' => UssImmutable::AUTHOR_EMAIL,
            'github_repository' => DashboardImmutable::GITHUB_REPO
        ]);
    }

}
