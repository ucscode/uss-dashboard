<?php

require_once __DIR__ . '/@init.php';

/**
 * Process An AJAX Request
 *
 * The route parameter helps to identify what kind of ajax request is being processed
 * The kind of request could be a:
 *
 * - Sign In Request
 * - Reset Password Request,
 * - Email Verification Request ETC.
 */

/**
 * VITAL INFORMATION
 *
 * This file is not intended for modules to process ajax request
 * Modules should send ajax request to a custom URL and then:
 * Add a focus expression pointing that URL
 *
 * EXAMPLE:
 *
 * ```js
 * $.post( "/path/to/ajax", {} );
 * ```
 *
 * ```php
 * Uss::route( "path/to/ajax", function() {
 *	 // handle ajax request
 * }, 'post' );
 * ```
 *
 * Nonetheless, If you still intend to call on this file to process custom request
 * Add a listener to the `udash:ajax` event directly without using a focus express
 * And also add a `route` index to the `$_POST` variable
 *
 * To avoid conflict with uss dashboard default ajax events,
 * The value of the route parameter must not match any of the default route value used by uss dashboard
 * Unless you intend to modify an existing uss dashboard ajax event.
 *
 * For example: if you want to add an antispam module to the registration page
 * Your code can be similar to the one below
 *
 * ```php
 * Events::addListener('udash:ajax', function() {
 *	 if( $_POST['route'] !== 'ud-signup' ) return;
 *	 // your antispam code here
 * });
 * ```
 *
 */

$authFiles = array(

    // Process login request

    'ud-signin' => "signin.php",

    //  Process registration request

    'ud-signup' => "signup.php",

    // Process reset (email) request

    'ud-reset' => "reset.php",

    // Process reset (password) request

    'ud-reset-v2' => "reset-v2.php",

    // Process (login) resend email request

    'ud-vcode' => "vcode-mail.php",

    // Process (login) resend email request

    'ud-notification' => "notification.php"

);


/**
 * Require an ajax file based on the route name
 * The route name is passed from the client side to inform the server
 * what kind of form is filled and what request should be processed
 *
 * The related file is then required to process the request
 */

foreach($authFiles as $route => $filename) {
    if(($_POST['route'] ?? null) !== $route) {
        continue;
    }
    require_once __DIR__ . "/routes/{$filename}";
};

/**
 * Execute the events
 */
Events::exec('udash:ajax');
