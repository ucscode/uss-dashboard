<?php

class UserLogoutController implements RouteInterface
{
    public function __construct(
        private PageManager $pageManager,
        private DashboardInterface $dashboard
    ) {
    }

    public function onload($match)
    {
        if(isset($_SESSION[UserInterface::SESSION_KEY])) {
            unset($_SESSION[UserInterface::SESSION_KEY]);
        };
        
        $endpoint = $this->pageManager->getCustom('endpoint') ?? null;

        if(!($endpoint instanceof UrlGenerator) && !is_string($endpoint)) {
            $endpoint = $this->dashboard->urlGenerator();
        };

        header("location: " . $endpoint);
        exit;
    }
};
