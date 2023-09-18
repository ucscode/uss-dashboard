<?php

defined('ROOT_DIR') || die('Invalid Registration Channel');

Uss::instance()->route($pageUnit['route'], function () use ($pageUnit) {

    // Allow user to view page without being logged in
    $this->enableFirewall(false);

    // Get the registration form
    $formInstance = $this->getConfig('forms:register');

    // Handle the submission
    $formInstance->handleSubmission();

    // Display the form content
    $this->render($pageUnit['template'], [
        'form' => $formInstance
    ]);

});
