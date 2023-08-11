<?php


class Udash extends UdashAbstract
{
    /**
     * Dynamic configuration storage for uss dashboard
     *
     * The dashboard configuration is updated or retrieved by using the `Udash::config()` method
     * Temporary data can also be save into the configuration file and used globally
     *
     * @var array $config
     * @see config
     */
    private static $config = array(

        # Display the dashboard sidebar

        "sidebar" => true,

        # Display the footer

        "footer" => true,

        # Hide everything! Display a completely blank page

        "blank" => false,

        /**
         * Authorize user to view the dashboard panel
         *
         * If a user is not logged-in, this will always convert to `false` even if set to `true`.
         * However, if a user is logged-in, access to the dashboard can be restricted by setting this to `false`.
         */
        "auth" => false,

        /**
         * The content to display to restricted users
         *
         * By default, if "auth" is set to `false`, the user will be presented with the system's default login page
         * or a page dedicated to a specific URL such as registeration page or reset password page.
         * However, you can display a custom login page by declaring a `callback` as the `auth-page` value
         */
        "auth-page" => null,

        /**
         * The main intent of the "debug" option has not been fulfilled
         * However, activating it in development mode may ease some process
         */
        "debug" => false

    );


    /**
     * A temporary storage medium for uss dashboard
     *
     * This method saves and retrieve data from the private `Udash::$config` property
     * Data saved with this method are used to alter the behaviour and appearance of the dashboard
     *
     * Usage:
     * - If $property is a string and $value is supplied, the information is saved
     * - If $property is a string and $value is not supplied, the value that matches the key will be returned
     * - If $property is null, an array containing a list of all configurations will be returned
     * - If $property is an array, the private `Udash::$config` data will be loaded with the key and values available in the array
     *
     * @param array|string|null $property
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws Exception if trying to set or get a value using an invalid index
     */
    public static function config(?string $property, $value = null)
    {

        if(is_array($property)) {
            self::$config = array_merge(self::$config, $property);
        } else {

            // test for property validity
            if(!self::can_be_string($property)) {
                throw new Exception("trying to set or get value using index of type " . getType($property));
            }

            // check if property is null
            if(is_null($property)) {
                return self::$config;
            }

            // if $value is not supplied
            if(func_num_args() === 1) {
                return self::$config[ $property ] ?? null;

                // else update configuration settings;
            } else {
                self::$config[ $property ] = $value;
            }

        };

    }


    /**
     * view ID
     *
     * Since most content has to be wrapped around the dashboard template
     * The view ID is the index for uss dashboard template
     * It helps to prevent the UI events of external modules from executing outside the body of the uss dashboard template
     */
    public const view_id = 49000;


    /**
     * Display the dashboard
     *
     * Within this method is the `Uss::view()` method which is responsible for displaying output
     * Therefore, `Uss::view()` and `Udash::view()` cannot be called together as only the first executed method will render output
     * After uss dashboard has been completely configured, use this method to display the output
     *
     * @param callable $func A callable to display content within the dashboard page
     * @return null
     */
    public static function view(callable $func)
    {

        /**
         * Before a user can see the dashboard,
         * self::$config['auth'] must be set to `TRUE`
         */
        if(!Uss::$global['user'] || !self::$config['auth']) { // If authentication is disapproved

            /**
             * Prepare a page blank
             */
            self::$config['blank'] = true;

            /**
             * Get the assigned authentication page;
             * `auth-page` should be a callable that will display an output if authentication is not approved
             */
            $func = self::$config['auth-page'];

            /**
             * If `auth-page` is not supplied or is not a valid callable
             * Then, user synthetics private `Udash::defaultAuthPages()` method will be used instead
             */
            if(!is_callable($func)) {
                $func = \Closure::fromCallable([ __CLASS__, "defaultAuthPages" ]);
            }

        } else {

            /**
             * Define a nonce based on session ID
             *
             * The nonce can be accessed through javascript and be used securely to prevent malicious attact.
             * However, it may be even more secure to create a custom nonce for your platform
             */
            Uss::console('Nonce', Uss::nonce($_SESSION['uss_session_id']));

            /**
             * The user title
             * This is a name displayed at point `%{user.title}` on the dashboard to reference user
             */
            Uss::tag('user.title', Uss::$global['user']['username'] ?: Uss::$global['user']['email'], false);

        }

        /**
         * Configure Basic Template Engine Tags
         */
        Uss::tag('udash.url', Core::url(ROOT_DIR . "/" . UDASH_ROUTE));
        Uss::tag('udash.ajax', Core::url(Udash::AJAX_DIR . '/@ajax.php', true));


        // Modify Interface & Render Content

        self::renderContent($func);

    }

    private static function renderContent(callable $func)
    {

        # >> HEAD SECTION

        Events::addListener('@head:after', function () {

            //Require HTML <head/> scripts
            require_once self::VIEW_DIR . '/bundle.head.php';

        }, EVENT_ID . self::view_id);


        # >> BODY SECTION

        Events::addListener('@body:before', function () {

            // Automatically remove sidebar if blank page is enabled

            if(self::$config['blank']) {
                self::$config['sidebar'] = false;
            }

            // If sidebar is enabled, include the sidebar

            if(self::$config['sidebar']) {
                require_once self::VIEW_DIR . '/sidebar.php';
            } else {
                // Update template to fill screen width
                $mainclass = Uss::tag('udash.main.class');
                Uss::tag('udash.main.class', trim("full-width {$mainclass}"));
            };

            // Get the template header

            require_once self::VIEW_DIR . '/header.php';

        }, -(self::view_id));


        Events::addListener('@body:beforeAfter', function () {

            // Get the template footer
            require_once self::VIEW_DIR . '/footer.php';

        }, self::view_id);


        Events::addListener('@body:after', function () {

            // Require HTML <body/> scripts
            require_once self::VIEW_DIR . '/bundle.body.php';

        }, EVENT_ID . self::view_id);


        /**
         * Render the content to browser
         */
        Events::addListener('//udash//view', function () use ($func) {
            Uss::console('@RE-POST', self::$config['debug']);
            Uss::view($func);
        });

    }

    /**
     * The method displays content on-screen for non-login users
     * if `auth-page` offset is not callable
     *
     * @ignore
     */
    protected static function defaultAuthPages()
    {

        /**
         * Get the default AUTH directory;
         *
         * The `AUTH` directory is a sub-directory of the view directory
         * It holds files responsible for the layout of authentication pages such as:
         * - Login page
         * - Registration page
         * - Reset Password Page
         */
        $auth_dir = Udash::VIEW_DIR . "/AUTH";

        /**
         * Default Tags
         *
         * Prepare the template tags
         * In contrast, do not overwrite any tag if it has already been declared by a module
         */
        Uss::tag('col.row', 'row g-0 auth-row', false);
        Uss::tag('col.left', 'col-lg-6', false);
        Uss::tag('col.right', 'col-lg-6', false);
        Uss::tag('auth.container', 'auth-wrapper', false);

        /**
         * Now create the Interface
         * Based on the URI path
         */
        switch(Uss::query(1)) {

            case 'signup':

                /**
                 * Check if signup page is disabled
                 */
                $enabled = empty(Uss::$global['options']->get('user:disable-signup'));

                /**
                 * Require page based on signup configuration
                 */
                require ($enabled) ? $auth_dir . "/signup.php" : $auth_dir . "/signup-closed.php";

                break;

            case 'reset':

                /**
                 * Verify the reset code
                 */
                require $auth_dir . "/@verify-reset.php";

                /**
                 * Verification of the reset code determines how the page should display
                 *
                 * - not verified: displays an input to receive email reset code
                 * - verified: display inputs to enable you reset password
                 */
                require $auth_dir . "/reset.php";

                break;

            default:

                /**
                 * Verify the email link that was clicked
                 */
                require $auth_dir . "/@verify-email.php";

                /**
                 * Import the sign-in page
                 */
                require $auth_dir . "/signin.php";

                break;

        };

        /**
         * Render the template
         *
         * Modules may also render custom contents by overriding the events or template tag within the template file
         * Allowing for 100% control over how your platform should work
         */
        require $auth_dir . "/template.php";

    }

}
