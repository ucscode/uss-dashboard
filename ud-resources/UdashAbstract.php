<?php
/**
 * uss dashboard abstraction class
 *
 * The uss dashboard abstract class is an extensible class containing method that are relevant for managing and maintaining the dashboard module.
 *
 * Extending this class is however not relevant as you can easily access the properties or method of the class through the global `udash` class which already inherit this class
 *
 * Every method and property existing in `udash_abstract` class are static and thus can be globally accessed
 *
 * @author ucscode
 */

defined('UDASH_DIR') or die;


abstract class UdashAbstract
{
    /**
     * VIEW_DIR
     *
     * The uss dashboard view directory
     * This is where the dashboard user interface files are contained. Such as:
     * - Footer
     * - Header
     * - Email Template
     * - Notification box
     * - Sidebar
     *
     * And any built in default component that comes directly with uss dashboard
     *
     * @var string
     */
    public const VIEW_DIR = UDASH_DIR . "/ud-view";

    /**
     * RES_DIR
     *
     * The uss dashboard resource directory
     * This directory contains important class files and libraries used by user synthetics dashboard
     *
     * @var string
     */
    public const RES_DIR = UDASH_DIR . "/ud-resource";

    /**
     * ASSETS_DIR
     *
     * The uss dashboard assets directory
     * This directory contains css files, javascript files, images and third party libraries
     *
     * @var string
     */
    public const ASSETS_DIR = UDASH_DIR . "/ud-assets";

    /**
     * AJAX_DIR
     *
     * The uss dashboard ajax directory
     * This directory contain files which are responsible for handling ajax request such as:
     *
     * - Registering new users
     * - Sending Push notifications
     * - Delivering emails etc
     *
     * @var string
     */
    public const AJAX_DIR = UDASH_DIR . "/ud-ajax";

    /**
     * PAGES_DIR
     *
     * The uss dashboard pages directory
     * This directory contain files which helps to render several pages such:
     *
     * - Profile page
     * - Hierarchy page etc
     *
     * @var string
     */
    public const PAGES_DIR = UDASH_DIR . "/ud-pages";

    /**
     * Returns a PHPMailer instance
     *
     * The method checks for configuration detail in the database and uses the detail to set up the PHPMailer instance before returing it.
     *
     * Hence, information such as sender name, sender email or SMTP information does not need to be set if the settings are already present in the database. However, if a custom change needs to be made, then the returned instance should be altered.
     *
     * @param bool $throwException
     *
     * @return object $PHPMailer
     */
    public static function PHPMailer(bool $throwException = false)
    {

        $PHPMailer = new \PHPMailer\PHPMailer\PHPMailer($throwException);
        $PHPMailer->isHTML(true);
        $PHPMailer->CharSet = 'UTF-8';

        $options = Uss::$global['options'];

        if($options->get('smtp:state') === 'custom') {
            $PHPMailer->isSMTP();
            $PHPMailer->SMTPDebug = 0;
            $PHPMailer->SMTPAuth = true;
            $PHPMailer->SMTPSecure = $options->get('smtp:security');
            $PHPMailer->Host = $options->get('smtp:server');
            $PHPMailer->Username = $options->get('smtp:username');
            $PHPMailer->Password = $options->get('smtp:password');
            $PHPMailer->Port = (int)$options->get('smtp:port');
        };

        $sender =  $options->get('email:from');
        if(empty($sender)) {
            $sender = $options->get('email:admin');
        }

        $PHPMailer->setFrom($sender, $options->get('site:title'));

        return $PHPMailer;

    }


    /**
     * Send confirmation email to a user
     *
     * This method sends an email to users that needs to validate their email.
     * The user will be provided with a link on their inbox and the email will be confirmed only after the link is
     * clicked and tested to match the security key associated to the email
     *
     * ## Use Cases:
     * - To confirm that a new email is valid or
     * - To confirm that an email being updated is valid
     *
     * If argument 2 (the old email) is not supplied, it indicates that the email being confirmed is new
     * Otherwise, it indicates an update to an existing email
     *
     * Note: If the old email is supplied, then the method will search and get a user by the old email.
     * Else, it will search for a user by the new email.
     *
     * If no user exists with the searched email, then the method will return `NULL`
     *
     * @param string $new_email The email that needs to be confirmed
     * @param string|null $old_email The email that should be updated (replaced) in database if the new email is valid
     *
     * @return boolean|null Confirms that the user exists and the email was successfuly sent
     */
    public static function send_confirmation_email(string $new_email, ?string $old_email = null)
    {

        $prefix = DB_TABLE_PREFIX;

        /**
         * Get a user by old email (for email updating).
         * Else, get the user by new email if old email is not supplied (for email confirmation)
         */
        $user = self::fetch_assoc("{$prefix}_users", $old_email ?? $new_email, 'email');

        // return false if no user was found
        if(!$user) {
            return;
        }

        /**
         * Generate Confirmation Code and URL
         *
         * This code below is responsible for generating a confirmation code and URL that will be used in
         * the email verification process. The confirmation code is stored in the usermeta table.
         *
         * If the email is considered new, the confirmation key will be set as `v-code` (verification code).
         * Otherwise, if it is an email update, the confirmation key will be set as `v-code:update`,
         * followed by `v-code:email` which stores the new email that will replace the old email upon confirmation.
         */


        /**
         * Generate a random confirmation key
         */
        $length = 32;

        if(empty($old_email)) {

            /**
             * For new email
             * Generate a code and store the value using `v-code` key in the usermeta table
             * If code is confirmed, verify the existing email
             */
            $pallet = array( "v-code" => Core::keygen($length) );

            /**
             * Pattern:
             * new:{v-code}:{user-email}
             */
            $encoding = base64_encode("new:{$pallet['v-code']}:{$user['email']}");

        } else {

            /**
             * For updating email
             * Generate a code and save both the code and the new email in usermeta table
             * If code is confirmed, replace the old email with the new email
             */
            $pallet = array(
                "v-code:update" => Core::keygen($length),
                "v-code:email" => $new_email
            );

            /**
             * Pattern:
             * update:{v-code}:{user-email}
             */
            $encoding = base64_encode("update:{$pallet['v-code:update']}:{$user['email']}");

        };


        /**
         * Load the information into the usermeta table!
         * The user id becomes the reference id
         */
        foreach($pallet as $_key => $_value) {
            Uss::$global['usermeta']->set($_key, $_value, $user['id']);
        };

        /**
         * Generate the confirmation URL
         *
         * The URL will be forwarded to the email that needs to be confirmed
         * The email security key will be tested for a match to confirm the validation
         */
        $verify_url = Core::url(ROOT_DIR . '/' . UDASH_ROUTE . "?v={$encoding}");

        /**
         * X2Client By Ucscode is a library that makes email template creation more easier
         * @link https://github.com/ucscode/X2Client
         * For more information:
         * @see X2Client
         */
        $maildir = self::VIEW_DIR . "/MAIL";

        /**
         * Get the email Template!
         *
         * At the moment, there is no option provided to dynamically update templates
         * If you intend to use a different template, you have to overwrite the template file in the mail directory
         *
         * However, in future, there may be a different model to allow you dynamically change template without overwriting the uss dashboard default email template
         *
         * Another option will be to extend the `udash_abstract` class and overwrite this method (not recommended)
         *
         * @var strng
         */
        $template = require($maildir . "/template.php");


        /**
         * Get the email message!
         *
         * @var string
         */
        $message = require($maildir . (!$old_email ? "/confirm-email.php" : "/reconfirm-email.php"));


        /**
         * Convert the Code into HTML Table
         *
         * Essential for Multiple Email Client Compactibility
        */

        $x2client = new X2Client($message);
        $message = $x2client->render("body > table");

        // replace variables within the email message;

        $message = Core::replace_var($message, array(
            'email' => $new_email,
            'href' => $verify_url
        ));


        // replace variables within the email template

        $__body = Core::replace_var($template, array(
            'content' => $message,
            'footer' => "You are receiving this email because you signup up at " . Uss::$global['title']
        ));


        /**
         * Initialize PHPMailer Instance
         *
         * The PHPMailer is fully configured for sending email
         * The main focus should be:

         * - Receiver address
         * - Email Subject
         * - Email Body
         *
         * And your email is ready for delivery
         *
         * @var object
         */

        $PHPMailer = self::PHPMailer();

        $PHPMailer->addAddress($new_email);
        $PHPMailer->Subject = 'Verify Your Email';
        $PHPMailer->Body = $__body;

        // Send it!

        return $PHPMailer->send();

    }


    /**
     * Send Password Reset Email
     *
     * When a (non-logged in) user tries to reset password from outside the dashboard,
     * The user is required to verify the process before they can change their password
     * This method sends a password reset email to client before allowing them to view the password reset form
     *
     * @param string $email
     *
     * @return bool|null
     *
     */
    public static function send_pwd_reset_email(string $email)
    {


        /**
         * Get the account associated to the email address;
         *
         * Will return null if no user was found
         */
        $prefix = DB_TABLE_PREFIX;
        $user = self::fetch_assoc("{$prefix}_users", $email, 'email');
        if(!$user) {
            return null;
        }

        /**
         * Generate a reset code
         *
         * The code will be confirmed when the link in the email is clicked
         * If the code in the link matches the generated code, user will be prompted to reset their password
         *
         * The code is only valid for a limite period of time.
         */
        $r_code = Core::keygen(45);

        /**
         * Save the reset code in the user meta
         * The code will be tested for password reset approval
         */
        Uss::$global['usermeta']->set("r-code", $r_code, $user['id']);


        // Generete the reset link;

        $encode = base64_encode("{$r_code}:{$user['email']}");

        $r_url = Core::url(ROOT_DIR . '/' . UDASH_ROUTE . "/reset?v={$encode}");


        /**
         * Prepare the email interface
         *
         * Get the uss dashboard email template
         * The get reset password email
        */

        $maildir = self::VIEW_DIR . "/MAIL";

        $template = require($maildir . "/template.php");
        $message = require($maildir . "/reset-email.php");

        $x2client = new X2Client($message);
        $message = $x2client->render('body > table');


        // Replace variables within the message

        $message = Core::replace_var($message, array(
            "href" => $r_url
        ));

        // Replace variables within the template

        $__body = Core::replace_var($template, array(
            "content" => $message,
            "footer" => 'Secure your account and stay safe!'
        ));

        /**
         * PHPMailer instance
         *
         * Initialize a configured PHPMailer instance
         * add the receiver email
         * add the subject
         * add the body
         */

        $PHPMailer = self::PHPMailer();
        $PHPMailer->addAddress($user['email']);
        $PHPMailer->Subject = "Reset Account Password";
        $PHPMailer->Body = $__body;

        // send the email
        return $PHPMailer->send();

    }


    /**
     * A Short Cut to get a single row of data from any table on the database
     *
     * Sometimes, you only need to fetch a single row of data base on simple search terms like:
     * - Getting a user by ID
     * - Gettting a particular product by it's unique code etc
     *
     * You don't always have to write a `SELECT` query and then fetch the associate
     * The fetch_assoc method accepts a `tablename` and a single unit of value which it will use to retreive the data in one shot!
     *
     * Note: If multiple rows has the same column value, it returns the only the first existing row that was found
     *
     * Example: Rather than doing this
     * ```php
     * $SQL = "SELECT * FROM my_table WHERE username = 'ucscode'"
     * $result = $mysqli->query( $SQL );
     * $data = $result->fetch_assoc();
     * ```
     *
     * You can equally to this:
     * ```php
     * $data = udash_abstract::fetch_assoc( 'my_table', 'ucscode', 'username' );
     * ```
     *
     * Please keep in mind that you cannot call `udash_abstract::fetch_assoc` directly as `udash_abstract` is an abstract class and needs to be extended. Use the `Udash::fetch_assoc` instead.
     *
     * @param string $tablename The name of the database table
     * @param string|null $value The value of a column you want to retreive
     * @param string $column The name of the column (default is `id`)
     *
     * @return array|null
     *
     */
    public static function fetch_assoc(string $tablename, ?string $value, string $column = 'id')
    {

        /**
         * @var MYSQLI $mysqli
         * @ignore
         */
        $mysqli = Uss::$global['mysqli'];

        /**
         * Sanitize the string
         */
        $column = $mysqli->real_escape_string($column);
        $value = $mysqli->real_escape_string($value);
        $tablename = $mysqli->real_escape_string($tablename);

        /** Validate the type of value being passed */
        $resultant = is_null($value) ? " IS NULL " : " = '{$value}'";

        /** Create the SQL Syntax */
        $SQL = SQuery::select($tablename, "{$column} {$resultant}");

        /** Capture the result */
        $result = $mysqli->query($SQL);

        return $result ? $result->fetch_assoc() : false;

    }


    /**
     * Upload a file
     *
     * In uss dasboard module, files are saved in the assets director
     * When uploading a file, several security measures has to be considered
     *
     * This method takes into consideration different kinds of file and test for several security breaches
     * to avoid injection of harmful contents.
     *
     * However, developers should take full responsibility of handling security and should not rely 100% on the safety of this method
     *
     * Parameter 1 is a string or array of accepted mime type that the uploaded file must match.
     * Example of mime types:
     *
     * - image/png
     * - image/*
     * - image/jpg|png|gif|webp
     * - application/pdf
     *
     * @param string|array $mime Accepted mime type
     * @param array $file A single file gotten by name from the global $_FILES variable
     * @param string $path The directory to save the file.
     * @param string|null $perfix A prefix to prepend at the beginning of the uploaded file name
     *
     * @return string|bool returns the path of the file or null if file could not be uploaded
     *
     * @throws Exception if argument 1 is neither an array nor string
     */
    public static function uploadFile($mimeTypes, array $file, string $path, ?string $prefix = null)
    {

        /**
         * Get the mime type(s)
         * If it's a string, convert it into an array
         */
        if(!is_array($mimeTypes)) {
            if(!is_scalar($mimeTypes)) {
                throw new Exception("Argument 1 must be a type of string or array");
            } else {
                $mimeTypes = [(string)$mimeTypes];
            }
        };

        /**
         * Filter the mime array
         * Ensuring that only string values a contained in the array list
         */
        $mimeTypes = array_values(array_filter($mimeTypes, function ($type) {
            if(!is_scalar($type)) {
                return false;
            }
            $type = (string)$type;
            return !empty($type);
        }));

        /**
         * Throw an exception:
         * If no mime type is specified
         */
        if(empty($mimeTypes)) {
            throw new Exception("No MIME type was specified in parameter 1");
        }

        /**
         * Check for error in the uploaded file
         */
        if(!empty($file['error'])) {
            return;
        }

        /*
            $size = $file['size'] / pow( 1024, 2 ); // kilobyte
            if( $size > 2.5 ) throw new Exception( "Image size should not be greater than 2.5 MegaByte" );

            $image = getimagesize($file['tmp_name']);
            if( !$image ) throw new Exception( "The uploaded file is not accepted" );
        */

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . sha1_file($file['tmp_name']) . ".{$extension}";
        $originalMIME = '';

        /**
         * Find a matching file mime;
         * @var string
         */
        $fileMIME = call_user_func(function () use (&$file, &$originalMIME, $mimeTypes) {

            /**
             * Get the mime type of the file
             * Allow PHP to scan the file internally and confirm the mime type
             */
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $originalMIME = explode("/", strtolower($finfo->file($file['tmp_name'])));

            foreach($mimeTypes as $unitMIME) {
                /**
                 * Break mime into an array.
                 * Example: ["image", "*"] | ["application", "pdf"]
                 */
                $unitMIME = array_map('trim', explode("/", strtolower($unitMIME)));

                // Check if the type matches that of the file
                if($originalMIME[0] === $unitMIME[0]) {

                    /**
                     * Get the mime extension
                     * If the extension is a wildcard; or
                     * The extension matches that of the file;
                     * return the mime;
                     */
                    $ext = $unitMIME[1] ?? null;
                    $ext = array_map('trim', explode('|', $ext));

                    if(preg_grep("#^(?:{$originalMIME[1]})|(?:\*)$#", $ext)) {
                        $match = implode("/", $originalMIME);
                        return $match;
                    };

                };
            };

        });

        $originalMIME = implode("/", $originalMIME);
        
        /**
         * Throw an exception
         * If the uploaded file does not match any supported mime type
         */
        if(empty($fileMIME)) {
            throw new Exception("415 — Unsupported Media Type ($originalMIME)");
        }

        # Get the relative & absolute path;

        $pathdata = self::getPathdata( $path, $filename );

        # Change File Permission

        //chmod($file['tmp_name'], 0777);

        # Move the uploaded file
        
        $moved = move_uploaded_file($file['tmp_name'], $pathdata['absolute']);

        if($moved) {
            
            # Change File Permission;

            chmod($pathdata['absolute'], 0776);

            return $pathdata['relative'];

        };

    }

    /**
     * Get the relative & absolute path for the uploaded file
     * @ignore
     */
    private static function getPathdata( string $path, string $filename ) {
        
        $pathdata = array();

        # Get the directory where file should be uploaded
        
        if(!Core::is_absolute_path($path)) {
            # Relative path will be uploaded to `Udash::ASSETS_DIR`
            $path = Core::abspath(self::ASSETS_DIR . "/{$path}");
        } else  $path = Core::abspath($path);
        
        # Recursively create any directory that does not exist

        $fullpath = call_user_func(function() use($path) {
            $pathname = explode("/", Core::rslash($path));
            $currentPath = '';
            foreach( $pathname as $dir ) {
                $currentPath .= $dir . DIRECTORY_SEPARATOR;
                if (!is_dir($currentPath)) {
                    if( is_writable(dirname($currentPath)) ) {
                        mkdir($currentPath, 0777, true);
                    };
                };
            }
            return substr($currentPath, 0, -1);
        });
        
        /**
         * If the directory does not exist, it means there was no sufficient permission 
         * given to the current user to create the directory automatically
         */
        if( !is_dir($fullpath) ) {
            /**
             * This would require creating the directory manually or
             * Give sufficient permission (0777) to the current user
             */
           // throw new Exception("Insufficient file system permission.");
        }

        # Get The Relative & Absolute Filepath

        $pathdata['relative'] = preg_replace("#^" . Core::rslash(MOD_DIR) . "/#i", "", $fullpath);
        
        if($pathdata['relative'] == $fullpath) {
            /**
             * For windows only!
             * Enforce path to start from forward slash. For example;
             * From "C:\a\b" To "/a/b"
             */
            $pathdata['relative'] = Core::url($fullpath, true);
        };

        $pathdata['relative'] = "{$pathdata['relative']}/{$filename}";
        $pathdata['absolute'] = "{$fullpath}/{$filename}";
        
        return $pathdata;

    }


    /**
     * returns a URL pointing to the user avatar
     *
     * In other words, get the profile picture of a user by the userid
     * It userid is null, the method returns the avatar of the current logged in user
     *
     * @param int|null $userid
     *
     * @return string
     *
     */
    public static function user_avatar(?int $userid = null)
    {

        # The default user avatar
        $default = self::ASSETS_DIR . '/images/user.png';

        # The uploaded user avatar
        $avatar = $userid ? Uss::$global['usermeta']->get('avatar', $userid) : null;

        # If no uploaded avatar, use default;
        if(!$avatar) $avatar = $default;
        else {
            # If uploaded avatar cannot be found, use default
            $avatar = MOD_DIR . "/{$avatar}";
            if( !is_file($avatar) )  $avatar = $default;
        };

        $avatar = Core::url($avatar);

        return $avatar;

    }

    /**
     * Check if a variable can be string
     *
     * @param mixed $var
     *
     * @return bool
     *
     */
    protected static function can_be_string($var)
    {
        if(!is_array($var)) {
            if(is_object($var) && method_exists($var, '__toString')) {
                return true;
            }
        };
        return (is_scalar($var) || is_null($var));
    }

    /**
     * uss dashboard style of displaying an error message
     *
     * This is not a highly significant method.
     * But in can be used to display error message in the look and feel of uss dashboard
     *
     * - If parameter is a string, it will be printed.
     * - If parameter is a callback, it will be called
     *
     * @param string|callback $var
     *
     * @return null
     *
     */
    public static function empty_state($var = 'Try searching with a different filter', string $title = 'No result found')
    { ?>
		<div class='text-center py-4 ud-empty-state container-fluid'>
			<h2 class='mb-3 fw-light text-uppercase'>
                <?php echo $title; ?>
            </h2>
			<img src='<?php echo Core::url(self::ASSETS_DIR . "/images/empty-state.webp"); ?>' width='400px' class='img-fluid user-select-none'>
			<div class='py-4'>
				<?php
                    if(is_callable($var)) {
                        $var();
                    } elseif(self::can_be_string($var)) {
                        echo $var;
                    }
        ?>
			</div>
		</div>
	<?php }


    /**
     * Delete a user account
     *
     * Deleting a user account will also delete the meta information of the user
     *
     * If a user meta data contains information that should not be lost, you can alternatively
     * update the user role and simulate a deletion effect
     *
     * The method will return false if the user was not deleted.
     * Else, it will return an array containing the deleted user information.
     * If the user does not exist, it will return an empty array;
     *
     * @param int $userid The id of the user
     *
     * @return bool|array
     *
     */
    public static function delete_user(int $userid)
    {

        // table prefix
        $prefix = DB_TABLE_PREFIX;

        /**
         * Get User By ID
         */
        $user = self::fetch_assoc("{$prefix}_users", $userid) ?? [];
        if(empty($user)) {
            return $user;
        }

        /**
         * Deletion SQL Query
         */
        $SQL = "
			DELETE FROM {$prefix}_users 
			WHERE id = {$user['id']}
		";

        /**
         * Delete User
         */
        $deleted = Uss::$global['mysqli']->query($SQL);

        /**
         * Return deleted user detail
         * Or false if deletion fails
         */
        return $deleted ? $user : false;

    }


    /**
     * Refresh the website variables
     *
     * This can be useful when variables are updated, such as site-title and those variables do not
     * immediately reflect on the `Uss::$global` property
     *
     * @ignore
     *
     */
    public static function refresh_site_vars()
    {
        foreach(['title', 'tagline', 'description', 'icon'] as $key) {
            $value = Uss::$global['options']->get("site:{$key}");
            if(!empty($value)) {
                if($key == 'icon') {
                    $value = Core::url(MOD_DIR . "/{$value}");
                }
                Uss::$global[ $key ] = $value;
            };
        };
    }


    /**
     * returns a bootstrap related color
     *
     * This method gets a bootstrap color based on commonly used status text
     *
     * For example, a cancelled or expired request are always depicted in red color &mdash; `danger`.
     * While successful requests is always depicted in green &mdash; `success`.
     *
     * ```php
     * // example:
     * echo "text-" . Udash::get_color('approved'); // text-success
     *
     * // example2:
     * echo "bg-" . Udash::get_color('failed'); // bg-danger
     * ```
     *
     * @param string|null $word
     *
     * @return string
     *
     */
    public static function get_color(?string $word)
    {

        // case insensitive name search;
        $word = strtolower(trim($word));

        // A list of color matching words
        $pallet = array(
            "primary" => ['open', 1],
            "success" => [ 'approve', 'resolve'],
            "danger" => ['fail', 'close', 'cancel', 'error', 'reject', 'decline', 0],
            "warning" => ['pending']
        );

        foreach($pallet as $color => $wordlist) {
            array_push($wordlist, $color);
            $regex = implode("|", $wordlist);
            $match = preg_match("/^(?:{$regex})/i", $word);
            if($match) {
                return $color;
            }
        };

        return 'secondary';

    }


    /**
     * Get the referrer of a user from URL Query
     *
     * This fetches the upline/parent of a user
     *
     * @param string $key
     *
     * @return [type]
     *
     */
    public static function get_sponsor(string $key = 'ref')
    {
        $usercode = $_GET[ $key ] ?? null;
        if(!$usercode) {
            $usercode = $_COOKIE['ussref'] ?? null;
            if(!$usercode) {
                return;
            }
        };
        $prefix = DB_TABLE_PREFIX;
        $sponsor = self::fetch_assoc("{$prefix}_users", $usercode, "usercode");
        if($sponsor && !headers_sent()) {
            /*
                This will save sponsor in cookie for 30 days!
                Hence, when the user comes again to register even without a referral link,
                The system will locate the sponsor automatically
            */
            setcookie('ussref', $usercode, (time() + (86400 * 30)), '/');
        };
        return $sponsor;
    }


    /**
     * Add or update push notification
     *
     * If argument 2 is supplied and a notification with the given id exists, it will be updated.
     * Else, a new notification will be created
     *
     * Push notification uses `MarkDown` language
     *
     * ```php
     * udash_abstract::notify(array(
     * 	"userid" => Uss::$global['user']['id'],
     * 	"message" => "**Hey**. [Click this link](https://github.com/ucscode) to view the author's page"
     * ));
     * ```
     *
     * @param array $data
     * @param int|null $id
     *
     * @return boolean
     *
     */
    public static function notify(array $data, ?int $id = null)
    {

        // presence of parameter 2 updates a notification with the given id

        $required = array('userid', 'message');
        $request = is_null($id) ? "deliver" : "update";

        foreach($required as $key) {
            if(!in_array($key, array_keys($data)) && empty($id)) {
                throw new Exception($key . " is required to {$request} notification");
            };
        };

        /**
         * Remove `id` if it's part of array index
         * Yea! You still haven't told me the reason why you would want to update the primary key!
        */

        if(isset($data['id'])) {
            unset($data['id']);
        }

        // sanitize data;

        foreach($data as $key => $value) {
            $value = trim($value);
            $data[ $key ] = Uss::$global['mysqli']->real_escape_string($value);
        };

        // prepare data for database entry;

        $prefix = DB_TABLE_PREFIX;

        if(!is_null($id)) {
            $SQL = SQuery::update("{$prefix}_notifications", $data, "id = {$id}");
        } else {
            $SQL = SQuery::insert("{$prefix}_notifications", $data);
        };

        // save notification ;

        $status = Uss::$global['mysqli']->query($SQL);
        return $status;

    }

    /**
     * Create Or Verify Password
     *
     * @param string $password The password to be hashed and the hashed string will be returned
     * @param string|null $hash If given, the method will test the password against the hash and return a boolean that verifies whether the hash is correct
     *
     * @return string|bool
     */
    public static function password(string $password, ?string $hash = null)
    {
        /**
         * Generate a new hash password
         */
        if(is_null($hash)) {
            return password_hash($password, PASSWORD_DEFAULT);
        }
        /**
         * Verify a password hash
         */
        return password_verify($password, $hash);
    }


    /**
     * Set Access Token
     *
     * Access Token is a set of data about a user which is saved in $_SESSION variable.
     *
     * It is a security algorithm than enables you to get a user by rationalized equations
     * rather than just using email & password to fetch user from database
     *
     * Using an access token is uss dashboard default way of authenticating a user
     * When an access token is created for a user,
     * the user can later be gotten by using the `getAccessTokenUser()` method
     *
     * > To maintain a stable use of user authorization in your modules, please use this method
     * > The tokenization algorithm may constantly change in newer versions to improve security
     *
     * ### Use cases
     *
     * If logged in user changes a core information (such as password)
     * Then the current access token of the user becomes invalid
     * This method can be used to reset the access token and prevent user from being logged out automatically
     *
     * This can also be used when a user has multiple account and needs to switch between the 2 accounts without being logged out first
     *
     * ## Layman term of explaining access token
     *
     * Access Token works by dedicating a unique key to a user
     * An then getting the user detail by access that unique key
     * The code below explains to concept:
     *
     * ```php
     * setAccessToken( 2, 'UniqueKeyForUser2' );
     * // A HASH will be generated and saved in the _SESSION
     * ```
     *
     * ```php
     * getAccessTokenUser( 'UniqueKeyForUser2' );
     * // An array containing information of user 2 will be returned
     * ```
     *
     * @param null|bool $userid If set to null, the asses token will be cleared
     * @param string $key The unique key that will be used to get the user
     *
     * @see getAccessTokenUser
     *
     */

    private static $ATKey = 'ud-aToken';

    public static function setAccessToken(?int $userid, string $key = 'default')
    {
        /**
         * By default, session already started by user synthetics
         * Save token in $_SESSION
         */
        $ATKey = self::$ATKey;

        $_SESSION[ $ATKey ] = $_SESSION[ $ATKey ] ?? [];

        /**
         * Clear access token if necessary
         */
        if(is_null($userid)) {
            return !self::clearAccessToken($_SESSION[ $ATKey ], $key);
        }

        /**
         * - Get database prefix
         * - Get user by id
         */
        $prefix = DB_TABLE_PREFIX;
        $user = self::fetch_assoc("{$prefix}_users", $userid);
        if(!$user) {
            return;
        }

        /**
         * The Access Token Algorithm
         */
        $userToken = $user['id'] . $user['usercode'] . $user['password'];

        self::clearAccessToken($_SESSION[ $ATKey ], $key);

        $_SESSION[ $ATKey ][$key] = array(
            'userid' => $user['id'],
            'hash' => hash('SHA256', $userToken)
        );

        return isset($_SESSION[ $ATKey ][ $key ]);

    }

    /**
     * @ignore
     */
    private static function clearAccessToken(array &$_SDATA, string $key)
    {
        /** Clear an access token */
        if(isset($_SDATA[ $key ])) {
            unset($_SDATA[ $key ]);
        }
    }

    /**
     * @param string $key The key to
     */
    public static function getAccessTokenUser(string $key = 'default')
    {

        $prefix = DB_TABLE_PREFIX;
        $ATKey = self::$ATKey;

        $data = $_SESSION[ $ATKey ] ?? null;

        /**
         * Check if the associated access token has been set
         */
        if(empty($data) || empty($data[$key])) {
            return;
        }

        $data = &$data[$key];
        array_walk($data, function (&$value) {
            // sanitize data
            $value = Uss::$global['mysqli']->real_escape_string($value);
        });

        $SQL = "
			SELECT * FROM {$prefix}_users
			WHERE id = '{$data['userid']}'
			AND SHA2( CONCAT(id, usercode, password), 256 ) = '{$data['hash']}'
		";

        return Uss::$global['mysqli']->query($SQL)->fetch_assoc();

    }

    /**
     * Get countries or country information
     *
     * If parameter 1 is null, an array of countries will be. setting parameter 2 to `true` will expand the list
     * If parameter 1 is string (country code), the country name will be returned. Or an array containing the country information will be returned if parameter 2 is set to `true`
     *
     * @param string|null $code The country code in ISO2 or ISO3
     * @param bool $expand returns an expanded list of country information including currency code,  continent, dial code etc
     *
     * @return mixed
     */
    public static function countries(?string $code = null, bool $expand = false)
    {

        /** Get all countries;*/
        $countries = json_decode(file_get_contents(ASSETS_DIR . "/JSON/countries.min.json"), true);

        /** ISO Format */
        $isoKey = "iso_2";

        /**
         * Country Code
         */
        if(!is_null($code)) {
            $code = trim(strtoupper($code));
            $length = strlen($code);
            if(!in_array($length, [2,3])) {
                throw new \Exception("Invalid Country Code - \"{$code}\"");
            }
            $isoKey = "iso_{$length}";
        }

        /**
         * Return Country List
         */
        if(!$expand) {
            $list = array_combine(array_column($countries, $isoKey), array_column($countries, 'name'));
            return (is_null($code)) ? $list : ($list[ $code ] ?? null);
        }

        if(is_null($code)) {
            return $countries;
        }

        /**
         * Search Country;
         */
        $key = array_search($code, array_column($countries, $isoKey));
        if($key === false) {
            return;
        }

        /**
         * Return The country
         */
        return $countries[$key];

    }

    /**
     * Load a file
     *
     * Conditionally load a file base on URL path or ajax request
     * This is very similar to focus. The difference is:
     *
     * 1. Focus uses regular expression while load uses normal pathname
     * 2. In parameter 2, focus accepts callable while load accepts filepath
     * 3. In parameter 3, focus work for distinctive request method while load accept a mixed value
     * 3. If path is focused, 404 page will not display. However, load will display 404 page unless Uss::view() is called
     * 4. Focus works only for matching URI while load work for strictly matching URL + Ajax Request
     * 5. Focus works only for the defined expression while load work for a matching URL and all descendant URL
     * 6. Focus is user synthetics default algorithm while Load is uss dashboard default expression
     *
     * Load Example:
     *
     * 	URL: "/dashboard/user"
     *
     *  - `/dashboard` = will not work
     * 	- `/dashboard/users` = will not work (users contained [s] at the end)
     * 	- `/dashboard/user/profile/picture` = will work (including for all descendants)
     *	- `path/to/@ajax.php` = will work (on ajax file)
     *
     *
     * When should you use load and focus?
     *
     * 1. Use `load` when you want to concentrate on a path and all it's subpath
     * 	- For Example, you want to build a forum, you can load your project into the "/forum" path and build there
     * 	- Hence, the forum will be fully available when the "/forum" path or subpath is visited
     *
     * 2. Use load if you want to have access to uss dashboard `@ajax.php` file
     *
     * 3. Use `focus` when you want to concentrate on a particular page or expression
     * 	- For Example, you can focus on `profile` page in the "/forum".
     * 	- Then, "/forum/profile" will display a profile page
     * 	- However, "forum/profile/username" will display a 404 error
     * 	- But you can also extend the focus using the expression `profile(/?\w+)?` to capture a username
     *
     * @param string $uriPath
     * @param string $resource
     * @param mixed $__arg An argument to be used within the loaded file or callable (use array for multiple values)
     *
     */
    public static function load(string $uripath, $resource, $__arg = null)
    {

        # Focus Route

        $uripath = array_filter(array_map('trim', explode("/", Core::rslash($uripath))));
        $match = array_slice(Uss::query(), 0, count($uripath));

        $type = getType($resource);

        $Exception = new \Exception(__METHOD__ . " — Parameter 2 must be a Callable or a valid File Path. ({$type}) given instead ");

        # Load Required Resource

        if($uripath === $match || self::is_ajax_mode()) {

            if(is_callable($resource)) {

                # Process callable

                return call_user_func($resource, $__arg);

            } elseif(is_string($resource)) {

                if(!is_file($resource)) {
                    throw $Exception;
                }

                return require $resource;

            };

            throw $Exception;

        };

    }

    public static function is_ajax_mode()
    {

        # verify script filename
        $is_ajax_file = Core::rslash($_SERVER['SCRIPT_FILENAME']) === Core::rslash(Udash::AJAX_DIR . "/@ajax.php");
        # verify request method
        $is_post_request = $_SERVER['REQUEST_METHOD'] === 'POST';
        # verify route index
        $is_routed = !empty($_POST['route']) && is_scalar($_POST['route']);
        # verify ajax definition
        $is_defined_ajax = defined("UDASH_AJAX");

        return $is_ajax_file && $is_post_request && $is_routed && $is_defined_ajax;

    }

    /**
     * returns an array contain associate array of mysqli result
     *
     * parameter 2 accepts a callable.
     * The callback receives 2 argument which is the `$value` and `$key` of the associate column
     * The `$value` can be changed and returned to produce a different dataset from the mysqli array
     *
     * This works very similarly to array_map function.
     *
     * @param MYSQLI_RESULT $result
     * @param callback $callback array mapping for the mysqli row
     */
    public static function mysqli_to_array(MYSQLI_RESULT $result, ?callable $callback = null)
    {
        $data = array();
        if($result->num_rows) {
            while($row = $result->fetch_assoc()) {
                if(is_null($callback)) {
                    $data[] = $row;
                } else {
                    $data[] = array_combine(
                        array_keys($row),
                        array_map($callback, array_values($row), array_keys($row))
                    );
                }
            };
        };
        return $data;
    }

}
