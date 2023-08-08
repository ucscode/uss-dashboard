<?php


defined('UDASH_AJAX') or die;


Events::addListener('udash:ajax', function () {

    /**
     * Get the user by email
     */
    $user = Udash::fetch_assoc(DB_TABLE_PREFIX . "_users", $_POST['email'], 'email');

    /** If no user is found, end the script */

    if(!$user) {
        Uss::exit(  "No account is associated to the email", false);
    }


    /**
     * Check if the email has already been verified!
     * If a `v-code` key does not exist on the user meta,
     * It means the email is verified
     */
    $vcode = Uss::$global['usermeta']->get('v-code', $user['id']);

    /** If email is verified, end the script */

    if(!$vcode) {
        Uss::exit(  "The email address has already been confirmed", false);
    }


    /**
     * Resend The confirmation email!
     * This will update the `v-code` key with a new one
     * Any previous email sent becomes invalid
     */
    $sent = Udash::send_confirmation_email($user['email']);

    /**
     * Get the response message
     */
    $message = $sent ? 'Please confirm the link sent to your email' : 'Sorry! email confirmation link could not be sent';

    $data = [];

    $result = [
        "status" => &$sent,
        "message" => &$message,
        "data" => &$data
    ];

    # Execution Phase

    Events::exec("udash:ajax/vcode", $result);

    /**
     * Inform the client
     * Then end the script
     */
    Uss::exit(  $result['message'], $result['status'], $result['data']);

}, 'ajax-vcode');
