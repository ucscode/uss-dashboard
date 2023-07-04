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
 * Every method sent to this file must be a `$_POST` request
 */


/**
 * Define Ajax Constant
 */
if(!defined('UDASH_AJAX')) {
    define('UDASH_AJAX', true);
}


/**
 * Get Request Method
 */
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('403 — Forbidden @ GET METHOD');
}


/**
 * Require a valid `route` parameter
 */
if(empty($_POST['route']) || !is_scalar($_POST['route'])) {
    die('400 — Bad Request @ NO ROUTE');
}


/**
 * Load User Synthetics Configuration File
 *
 * The configuration file will load all libraries, constants, resources and modules
 * But it will not load the index page which is responsible for displaying 404 error page
 *
 * The essence of loading the config file is to give modules the opportunity to make changes to certain events
 * However, modules are discouraged from printing anything on this page
 * As it will affect the normal JSON response that should be printed
 * Therefore, all direct output made by modules will be buffered and cleared
 *
 * Only event executions made through `@udash//ajax` listener can have printable content
 * The event should however be called globally and not within a focus expression
 */


/**
 * Output Buffering!
 *
 * By default, php uses output buffer on the start of a script and flushes at the end
 * Nonetheless, an end-user may configure the ini directive and set "output_buffering" to off
 *
 * In this case, we have to start the output buffering manually
 * we do this by confirming if the output buffer has not already been started
 */
if(!ob_get_level()) {
    ob_start();
}


/**
 * If there is an exception or error within the script
 * It will be displayed to enable developer fix it
 */
try {

    /** Load the configuration file */

    require_once realpath(__DIR__ . '/../../../') . "/uss-core/config.php";

} catch(Exception $e) {

    /** Re-Throw the exception */

    while(ob_get_level()) {
        ob_end_clean();
    }

    throw $e;
}

/**
 * After loading the config file
 * We need to clean all buffer as we expect absolutely zero output
 */

while(ob_get_level()) {
    ob_end_clean();
}
