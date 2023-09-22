<?php

defined('ROOT_DIR') || die('Invalid Registration Channel');

Uss::instance()->route($pageInfo['route'], function () use ($pageInfo) {

    $this->enableFirewall(false);

    $formInstance = $this->getConfig('forms:register');

    $formInstance->handleSubmission();
    
    $this->render($pageInfo['template'], [
        'form' => $formInstance
    ]);

});
