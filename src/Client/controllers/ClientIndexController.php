<?php

class ClientIndexController implements RouteInterface
{
    private Archive $page;

    public function __construct(Archive $page)
    {
        $this->page = $page;
    }

    public function onload(array $matches)
    {
        $ud = Ud::instance();
        $ud->render($this->page->get('template'));
    }

};
