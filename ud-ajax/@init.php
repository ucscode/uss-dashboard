<?php

/**
 * User Synthetics Dashboard Ajax Process
 *
 * @package uss.dashboard
 * @author ucscode
 *
 * You can call on this file to execute ajax request
 * However, I recommend using a focus expression to handle custom ajax request
 *
 * Note: This file does not accept `$_GET` request method
 */

# Define Ajax Constant
if(!defined('UDASH_AJAX')) {
    define('UDASH_AJAX', true);
}

# Check Request Method
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    die('403 — Forbidden @ GET METHOD');
}

# Require a valid `route` parameter
if(empty($_POST['route']) || !is_scalar($_POST['route'])) {
    die('400 — Bad Request @ NO ROUTE');
}


/**
 * Load User Synthetics Configuration File
 * Only event executions made through `udash:ajax` listener can have printable content
 * The event should however be called globally and not within a focus expression
 */

# Output Buffering!
if(!ob_get_level()) {
    ob_start();
}

# If there is an exception or error within the script, it will be displayed to enable developer fix it
try {

    # Load the Configuration file & Modules
    require_once realpath(__DIR__ . '/../../../') . "/uss-core/config.php";

} catch(Exception $exception) {

    # Re-Throw the exception 
    while(ob_get_level()) {
        ob_end_clean();
    }

    throw $exception;
}

/**
 * All buffered output will be cleared to ensure zero output
 * Unless debug mode is enabled
 */
if( !Udash::config('debug') ) {
    # Clear Output Buffer
    while(ob_get_level()) {
        ob_end_clean();
    }
};
