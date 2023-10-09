<?php

defined('ROOT_DIR') || die(':REGISTER');

class RegisterController implements RouteInterface
{
    public function __construct(private Archive $page)
    {

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
