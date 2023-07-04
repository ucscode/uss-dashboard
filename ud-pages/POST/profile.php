<?php


defined("UDASH_DIR") or die;

call_user_func(function () {

    /**
     * VERIFY REQUEST_METHOD
     */
    if($_SERVER['REQUEST_METHOD'] !== 'POST' || empty(Uss::$global['user'])) {
        return;
    }

    /**
     * VERIFY NONCE
     */
    if(!Uss::nonce('profile', $_POST['nonce'])) {

        /**
         * IN-SECURITY MESSAGE
         */
        Uss::console('@alert', "<i class='bi bi-shield-slash-fill me-1'></i> Security check failed");
    } else {

        /**
         * PROCESS PROFILE DETAIL
         */
        Events::addListener('udash:pages/profile.submit', function () {

            if($_POST['route'] !== 'profile') {
                return;
            }

            $user = &Uss::$global['user'];

            $prefix = DB_TABLE_PREFIX;

            /**
             * File upload will throw an exception
             * If the MIME of uploaded file does not match the MIME specified in the method
             * We must catch the exception
             */
            try {

                /**
                 * UPLOAD AVATAR
                 * --------------------
                 * 1. The accepted file MIME
                 * 2. The file array
                 * 3. The directory to upload the file into ( `Udash::ASSETS_DIR` is the ROOT directory )
                 * 4. A prefix to prepend to the filename
                 * ---------------------
                 */
                $avatar = Udash::uploadFile("image/jpg|png|jpeg|webp", $_FILES['avatar'], 'images/profile', "{$user['id']}-");

                /**
                 * Get only required data from the $_POST
                 */
                $data = array();
                array_walk_recursive($_POST, function ($value, $key) use (&$data) {
                    if(in_array($key, ['username', 'email'])) {
                        $value = strtolower($value);
                        $data[$key] = Uss::$global['mysqli']->real_escape_string($value);
                    }
                });

                /**
                 * Refill Email
                 * Then validate email
                 */
                if(!isset($data['email'])) {
                    $data['email'] = $user['email'];
                }

                if(!preg_match(Core::regex('email'), $data['email'])) {
                    throw new Exception("Invalid email address");
                }


                /**
                 * Confirm New Email Address
                 *
                 * It's great when a user verifies their email after signup
                 * Because it shows that the user email is not some sort or formulated trash.
                 * However, after the login process, a user may decide to change email to `fake@email.com`
                 * Probably to aviod receiving email such as subscription messages, notification etc
                 *
                 * To avoid unwanted email, the user has to reconfirm that the new email is correct!
                 * Hence, it's recommeded to force users to reconfirm their new email!
                */

                $reconfirm = !empty(Uss::$global['options']->get('user:reconfirm-email'));

                if($reconfirm && ($user['email'] !== $data['email'])) {
                    /**
                     * Send Confirmation Email
                     */
                    $update = Udash::send_confirmation_email($data['email'], $user['email']);

                    if($update) {
                        $message = "
							<div class=''>
								<i class='bi bi-envelope-at-fill text-muted me-1'></i> 
								Please click the link sent to your email to complete the update
							</div>
						";
                    } else {
                        $message = "
							<div class=''>
								<i class='bi bi-envelope-exclamation-fill text-warning me-1'></i> 
								Email confirm link could not be sent
							</div>
						";
                    };

                    $data['email'] = $user['email'];

                } else {
                    /**
                     * If user does not intent to proceed with new email
                     */
                    foreach(['v-code:update', 'v-code:email'] as $key) {
                        /**
                         * Remove the email verification keys
                         */
                        Uss::$global['usermeta']->remove($key, $user['id']);
                    };
                    $message = null;
                }


                /**
                 * UPDATE USER PROFILE
                 * Prepare SQL Query
                 */
                $SQL = SQuery::update("{$prefix}_users", $data, "id = {$user['id']}");

                /**
                 * Update Database
                 */
                $update = Uss::$global['mysqli']->query($SQL);

                /**
                 * Get Response Message
                 */
                if($update) {

                    $message = "
						<div class=''>
							<i class='bi bi-check-circle text-success me-1'></i> Profile Updated
						</div> {$message}
					";

                    /**
                     * Update user avatar
                     * If the user submitted any
                     */
                    if(!empty($avatar)) {
                        Uss::$global['usermeta']->set('avatar', $avatar, $user['id']);
                    }

                } else {
                    $message = "
					<div class=''>
						<i class='bi bi-x-octagon text-muted me-1'></i> Profile Not Updated
					</div> {$message}
				";
                }

            } catch(Exception $e) {
                /**
                 * Handle Exception
                 * Most likely, this would be caused by the `Udash::uploadFile` method
                 */
                $message = "<i class='bi bi-exclamation-triangle me-1'></i> " . $e->getMessage();
            };

            /**
             * SHOW MESSAGE IN MODAL BOX
             */
            Uss::console('@alert', $message);

        }, EVENT_ID . 'profile');


        /**
         * UPDATE USER PASSWORD
         */
        Events::addListener('udash:pages/profile.submit', function () {

            if($_POST['route'] !== 'password') {
                return;
            }

            $user = Uss::$global['user'];

            /**
             * Check if old password is correct
             */
            $approved = Udash::password($_POST['old_password'], $user['password']);

            if(!$approved) {
                $message = "<div class=''>
					<i class='bi bi-exclamation-triangle me-1'></i> Old password is wrong
				</div>";
            }

            /**
             * Check if password matches
             */
            elseif($_POST['password'] !== $_POST['confirm_password']) {

                $message = "<div class=''>
					<i class='bi bi-exclamation-triangle me-1'></i> Password does not match
				</div>";
            } else {

                $prefix = DB_TABLE_PREFIX;

                /**
                 * Get SQL Query
                 * Then update user password
                 */
                $SQL = SQuery::update("{$prefix}_users", array(
                    'password' => Udash::password($_POST['password'])
                ), "id = {$user['id']}");

                $update = Uss::$global['mysqli']->query($SQL);

                if($update) {

                    /** Renew access token */
                    Udash::setAccessToken($user['id']);

                    $message = "<div class=''>
						<i class='bi bi-lock me-1'></i> Password successfully updated
					</div>";

                } else {
                    $message = "<div class=''>
						<i class='bi bi-exclamation-triangle'></i> Password update failed
					</div>";
                };

            };

            /**
             * SHOW MESSAGE IN MODAL BOX
             */
            Uss::console('@alert', $message);

        }, EVENT_ID . 'password');


        /**
         * Event Execution
         * Requires `route` index in `$_POST` variable to function
         */
        Events::exec('udash:pages/profile.submit', null, null, function () {
            return !empty($_POST['route']);
        });

    };

});
