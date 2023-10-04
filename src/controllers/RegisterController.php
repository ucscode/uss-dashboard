<?php

defined('ROOT_DIR') || die(':REGISTER');

class RegisterController implements RouteInterface
{
    private UdPage $page;

    public function __construct(UdPage $page)
    {
        $this->page = $page;
    }

    public function onload($regex)
    {
        $ud = Ud::instance();

        $template = $this->page->get('template');
        $registerForm = $this->page->get('form');

        $formInstance = new $registerForm($this->page->name);
        $formInstance->handleSubmission();

        $ud->enableFirewall(false);

        $ud->render($template, [
            'form' => $formInstance
        ]);

    }

}
