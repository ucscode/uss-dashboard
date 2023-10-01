<?php

defined('ROOT_DIR') || die(':REGISTER');

Uss::instance()->route($pageInfo['route'], function () use ($pageInfo) {

    $this->enableFirewall(false);

    $formInstance = $this->getConfig('forms:register');

    $formInstance->handleSubmission();

    $this->render($pageInfo['template'], [
        'form' => $formInstance
    ]);

});
