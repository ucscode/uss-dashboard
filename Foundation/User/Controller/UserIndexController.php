<?php

class UserIndexController implements RouteInterface
{
    public function __construct(
        protected PageManager $pageManager,
        protected DashboardInterface $dashboard
    ) {
    }

    public function onload(array $matches)
    {
        $this->pageManager->getMenuItem('index', true)?->setAttr('active', true);

        $this->dashboard->render($this->pageManager->getTemplate(), [
            'official_website' => UssImmutable::PROJECT_WEBSITE,
            'title' => UssImmutable::PROJECT_NAME,
            'dev_email' => UssImmutable::AUTHOR_EMAIL,
            'github_repository' => DashboardImmutable::GITHUB_REPO
        ]);
    }

};
