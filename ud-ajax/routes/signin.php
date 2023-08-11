<?php


defined('UDASH_AJAX') or die;

/**
 * Add new ajax event
 */

Events::addListener('udash:ajax', function () {

    $status = !!($user = null);
    
    # Database prefix
    
    $prefix = DB_TABLE_PREFIX;

    /**
     * Sanitize the login
     * Prevent SQL Injection by escaping unwanted inputs
     */
    $_POST['login'] = Uss::$global['mysqli']->real_escape_string($_POST['login']);


    /**
     * Confirm the login input & database column
     *
     * The input name is called login because it accepts 2 type of value
     *
     * - username
     * - email
     *
     * If the login input does not match an email, then it is assumed to be a username
     */
    $column = preg_match(Core::regex('email'), $_POST['login']) ? 'email' : 'username';


    /**
     * fetch the user the input column
     * Thanks to the `udash_abstract::fetch_assoc`
     * It allows us get a user detail securely without running multiple query
     */
    $user = Udash::fetch_assoc("{$prefix}_users", $_POST['login'], $column);


    /**
     * Check if any matching user was found
     */

    if(!$user) {

        $message = "The login credential is incorrect";

    } else {

        /**
         * Verify the user password!
         * Using PHP's default password verification algorithm
         */
        $password = Udash::password($_POST['password'], $user['password']);

        /**
         * INCASE OF LOST PASSWORD!
         *
         * A situation where admin need to personally update the `PHPMYADMIN` table to regain access
         * Uss Dashboard allows `SHA256` password encryption
        */

        $password_alt = hash('SHA256', $_POST['password']) === $user['password'];


        /**
         * Check if any of the password matches
         * Else, return the same message as when the user wasn't even found
         */

        if(!$password && !$password_alt) {

            $message = 'The login credential is incorrect';

        } else {

            /**
             * User Approved!
             *
             * Now we need to check if the email is verified
             * This means, there should be no `v-code` key existing for the user in the usermeta table
            */
            $vcode = Uss::$global['usermeta']->get('v-code', $user['id']);

            /**
             * If the `v-code` data exists
             * Then it means the user is yet to verify the email
             */
            if($vcode) {

                $message = "Please confirm your email address to proceed!";

            } else {

                /**
                 * Login process successful
                 * Create an access token
                 */

                Udash::setAccessToken($user['id']);

                $status = !!($message = "<i class='bi bi-check-circle text-success me-1'></i> Login successful");


                /**
                 * Assign default role to the user!
                 * If the user has no existing role
                */

                if(empty(Roles::user($user['id'])::get_user_roles())) {

                    Roles::user($user['id'])::assign(Uss::$global['options']->get('user:default-role'));

                };

            };

        };

    };

    $data = array( "redirect" => Udash::config('signin:redirect') );

    $result = [
        "status" => &$status,
        "message" => &$message,
        "data" => &$data,
        "user" => $user,
    ];

    # Allow modules to make changes;

    Events::exec("udash:ajax/signin", $result);

    # Print Output and end the script
    
    Uss::exit( $result['message'], $result['status'], $result['data'] );

}, 'ajax-signin');
