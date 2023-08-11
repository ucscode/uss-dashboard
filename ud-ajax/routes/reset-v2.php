<?php


defined("UDASH_AJAX") or die;


Events::addListener('udash:ajax', function () {

    $status = false;

    /**
     * Verify Password Update Security
     *
     * Since this is an open space, even robots can fill the form and reset the password
     * We need to do some crafty things to minimize the chances of resetting password by unauthorized entity
     */

    if(empty($_POST['passport']) || empty($_POST['nonce'])) {
        Uss::exit(  "Suspicious Request", false);
    }


    /**
     * Now let's confirm the process
     * First, we prepare the prefix
     */

    $prefix = DB_TABLE_PREFIX;


    /**
     * Confirm that the password matches
     */
    if($_POST['password'] !== $_POST['confirm_password']) {
        $message = "Password does not match";
    } else {


        /**
         * Let's confirm the nonce
         * This ensures that the end-user is from a reliable source
         */
        if(!Uss::nonce($_SESSION['resetter'], $_POST['nonce'])) {

            $message = "The request could not be completed! <br> Please contact the support team";

        } else {

            try {

                /**
                 * Decode the passport!
                 * Use the passport to locate the user
                 */
                $passport = explode("-", str_rot13(base64_decode($_POST['passport'])));

                $user = Udash::fetch_assoc("{$prefix}_users", $passport[1]);


                /**
                 * Check if the user exists
                 */
                if(!$user) {
                    $message = "Authentication detail could not be discovered!";
                } else {

                    /**
                     * With the same passport
                     * Reconfirm the password reset key
                     */
                    $r_code = Uss::$global['usermeta']->get("r-code", $user['id']);

                    if(empty($r_code) || $r_code !== $passport[0]) {
                        $message = 'Invalid client to server request combination';
                    } else {

                        /**
                         * If the reset key is valid
                         * Update the user password
                         */
                        $_POST['password'] = Udash::password($_POST['password']);

                        /** The SQL Query */

                        $SQL = SQuery::update("{$prefix}_users", array(
                            "password" => $_POST['password']
                        ), "id = {$user['id']}");

                        /** Update Password */

                        $status = Uss::$global['mysqli']->query($SQL);

                        if($status) {

                            /**
                             * Remove the reset password confirmation key
                             */

                            Uss::$global['usermeta']->remove('r-code', $user['id']);

                            $message = "Password successfully updated! <br> You can login with your new password now";

                        } else {
                            $message = "Password updated failed";
                        }

                    };

                }

            } catch(Exception $e) {

                $message = 'Apologies! A critical error occured';

            }

        }

    };

    # Define the login page
    
    $data = array( "redirect" => Core::url(ROOT_DIR . '/' . UDASH_ROUTE) );

    $result = [
        "status" => &$status,
        "message" => &$message,
        "data" => &$data,
    ];

    # Module Execution Phase

    Events::exec("udash:ajax/reset-v2", $result);

    # Print the output and end the script
    
    Uss::exit( $result['message'], $result['status'], $result['data'] );

}, 'ajax-reset-v2');
