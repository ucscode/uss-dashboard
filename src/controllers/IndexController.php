<?php

defined('Ud::DIR') or die(':INDEX');

class IndexController implements RouteInterface
{
    private UdPage $page;

    public function __construct(UdPage $page)
    {
        $this->page = $page;
    }

    public function onload(array $matches)
    {
        $ud = Ud::instance();

        $ud->render($this->page->get('template'));
    }

};
