<?php

class UserIndexController implements RouteInterface
{
    private Archive $page;

    public function __construct(Archive $page)
    {
        $this->page = $page;
    }

    public function onload(array $matches)
    {
        $ud = UserDashboard::instance();
        $ud->render($this->page->get('template'));
    }

};
