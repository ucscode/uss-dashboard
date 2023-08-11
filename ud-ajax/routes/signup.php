<?php


defined("UDASH_AJAX") or die;

# ------------------------------------------

Events::addListener('udash:ajax', function () {

    $status = false;
    $userdata = null;

    # Trim all character

    array_walk_recursive($_POST, function(&$value) {
        $value = trim($value);
    });

    # The table prefix
    
    $prefix = DB_TABLE_PREFIX;


    if( !preg_match(Core::regex('email'), $_POST['email']) ) {
        
        # Check if the email address is valid

        $message = "The email address is not valid";

    } elseif( isset($_POST['username']) && !preg_match("/^\w+$/i", $_POST['username']) ) {

        # Check if username is valid

        $message = "Username can only contain number, letter and underscore";

    } elseif ($_POST['password'] !== $_POST['confirm_password']) {

        # Check if password matches;

        $message = "Password does not match";

    } else {

        # Check if the email address already exists;
        
        $user = Udash::fetch_assoc("{$prefix}_users", $_POST['email'], 'email');

        if($user) {
            
            # If a user if found, then, the email exists
            
            $message = "The email already exists";

        } else {

            # Check if the username already exists;

            $username = $_POST['username'] ?? null;
            $user = Udash::fetch_assoc("{$prefix}_users", $username, 'username');

            /**
             * If username is set & A user is found
             * Then, the username exists
             */
            if( !empty($username) && $user ) {

                $message = "The username already exists";

            } else {

                /**
                 * If parent ID is supplied:
                 * Then: confirm that the parent exists!
                 */
                if(isset($_POST['parent']) && !empty(Uss::$global['options']->get('user:affiliation'))) {

                    # Get the parent

                    $parent = Udash::fetch_assoc("{$prefix}_users", $_POST['parent']);

                    if($parent)  $parent = $parent['id'];
                    
                } else $parent = null;

                /**
                 * Great!
                 * All validation process has been confirmed!
                 */
                $userdata = array(
                    "email" => $_POST['email'],
                    "password" => Udash::password($_POST['password']),
                    "username" => $username,
                    "usercode" => Core::keygen(mt_rand(5, 7)),
                    "parent" => $parent
                );

                /**
                 * Insert into database
                 * sQuery will auto sanitize the input
                 */
                $SQL = SQuery::insert("{$prefix}_users", $userdata, Uss::$global['mysqli']);

                /**
                 * Insert the user into database
                 * Then get the userid immediately
                 */
                $status = Uss::$global['mysqli']->query($SQL);

                $userdata['id'] = Uss::$global['mysqli']->insert_id;


                # If status == true 

                if($status) {

                    # ssign a role to the new user!

                    $defaultRole = Uss::$global['options']->get('user:default-role');

                    $assigned = Roles::user($userdata['id'])::assign($defaultRole);

                    # Clear Access Token!
                    
                    Udash::setAccessToken(null);

                    # The success message
                    
                    $message = "<i class='bi bi-check-circle text-success me-1'></i> Your registration was successful";
                    

                    # Send verification email

                    $verify_mail = !empty(Uss::$global['options']->get('user:confirm-email'));

                    if($verify_mail) {

                        /**
                         * Send a new confirmation link to the user
                         * The user will be able to login on after verifying the link
                         */
                        $sent = Udash::send_confirmation_email($userdata['email']);

                        $className = 'mt-3 pt-3 border-top fs-14px';

                        if($sent) {

                            $message .= "
                                <div class='{$className} text-success'> 
                                    Please confirm the link sent to your email
                                </div>
                            ";

                        } else {

                            $message .= "
                                <div class='{$className} text-danger'> 
                                    Email confirmation link failed to send. <br> Try requesting for a new link in the login form
                                </div>
                            ";

                        };

                    };

                    # ------- [ End Email Verification ] -----------

                } else {

                    /**
                     * If user detail was not inserted into database
                     * Output a failure message
                     */
                    $message = "<i class='bi bi-x-circle text-danger me-1'></i> The registration was not successful";

                }

            } // username doesn't exits

        } // email doesn't exist

    }; // all conditions are met


    $data = [ 
        "redirect" => Udash::config('signup:redirect') ?? Core::url(ROOT_DIR . '/' . UDASH_ROUTE) 
    ];

    $result = [
        "status" => &$status,
        "message" => &$message,
        "data" => &$data,
        "user" => $userdata,
    ];

    /**
     * After the signup process, this event will be executed.
     * Thus, allowing modules to make changes based on their specific need
     */
    Events::exec("udash:ajax/signup", $result); 

    # Print the output and end the script
    
    Uss::exit( $result['message'], $result['status'], $result['data'] );
    
}, 'ajax-signup');
