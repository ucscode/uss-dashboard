<?php

defined('ROOT_DIR') || die(':NOTIFICATION');

class NotificationController implements RouteInterface
{
    public function __construct(private UdPage $page)
    {

    }

    public function onload($pageInfo)
    {

        $ud = Ud::instance();

        $ud->render($this->page->get('template'), [

        ]);

    }

};
