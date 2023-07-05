<?php

/**
 * Uss Dashboard Initialization
 *
 * This initializes key components of the dashboard, including the menu, logged user section, user meta,
 * access token functionality, and affiliation management. These sections are essential for user interaction,
 * profile management, access control, and affiliation-related operations within the User Synthetics system.
 */
defined("UDASH_DIR") or die;


/**
 * Dashboard Menu Instantiation
 *
 * The `Menu` object is instantiated to control the flow of content within the sidebar of the User Synthetics dashboard.
 * It is powered by the `Menufy` class, which provides the necessary functionality for managing and rendering the menu structure.
 *
 * @var Menufy
 */
Uss::$global['menu'] = new Menufy();


/**
 * Logged-In User Declaration
 *
 * This variable stores information about the currently logged-in user.
 * If a user is logged in, their details will be stored in this variable.
 * If no user is logged in, the variable defaults to null.
 *
 * @var array|null
 */
Uss::$global['user'] = null;


/**
 * Default Dashboard Configuration Options
 *
 * This variable holds the default configuration options for the dashboard.
 * It is managed by the `pairs` class and can be easily updated using the `Uss::$global['options']` variable.
 *
 * To update these options through a user interface instead of programmatically, you can install the User Synthetics Admin Panel module.
 */

$udash_config = array(

    # Temporarily disable the signup page.
    'user:disable-signup' => 0,

    # Collect username at the point of registration
    'user:collect-username' => 0,

    # Send a confirmation email to users at the point of registeration
    'user:confirm-email' => 0,

    /**
     * Prevent users from updating their email when they login to their dashboard account
     * This can be useful when a user has confirmed their email and you don't want them updating it into a fake email
     * However, it's not recommended because if a user lost access to their email, they cannot be able to update it
     */
    'user:lock-email' => 0,

    # Force users to reconfirm their email if they change it
    'user:reconfirm-email' => 1,

    # Default user role when registering
    'user:default-role' => 'member',

    # Allow affiliation link and referral program
    'user:affiliation' => 0,

    # Automatically delete a user from the system if their email in not confirmed with a specific number of days
    'user:auto-trash-unverified-after-day' => 7,

    /**
     * The administrator email address
     * This will be used as the default email address for sending emails to clients
     */
    'email:admin' => 'admin@' . $_SERVER['SERVER_NAME'],

    /**
     * Simple Mail Transfer Protocol (SMTP) Configuration
     *
     * This variable controls the SMTP configuration for sending emails.
     * It accepts values of either `default` or `custom`.
     *
     * - `default`: Uses the default server email configuration setting.
     * - `custom`: Allows you to set up your own SMTP server.
     *
     * To easily update this option, you can install the User Synthetics Admin Panel module, which provides a user-friendly interface for managing SMTP settings.
     */
    'smtp:state' => 'default'
);


/**
 * Push the configuration options into database
 */
foreach($udash_config as $key => $value) {

    /**
     * Check if the data exists already
     * Otherwise, add the configuration setting
     */
    $data = Uss::$global['options']->get($key);

    if(is_null($data)) {
        Uss::$global['options']->set($key, $value);
    }

}


/**
 * Hey! There was once a "REMEMBER ME" authentication login code here!
 *
 * The remember me authentication process was removed!
 * Though secured! A "remember me" identifier is valid for days and this long validity period poses security risk
 * The process was removed not based on lower security measures but for a different module to handle it instead
 * Hence, a module dedicated for that purpose will perform better at security measurement!
 */

/**
 * Authenticate user Login
 * @see \UdashAbstract::getAccessTokenUser()
 */
Uss::$global['user'] = Udash::getAccessTokenUser();

/**
 * Update `Uss::$global` Variables
 *
 * This function updates certain `Uss::$global` variables.
 * Refreshing the site variable can be useful when a configuration setting is updated after the default settings have already been loaded. This ensures that users view the most up-to-date settings without having to manually reload the page.
 */
Udash::refresh_site_vars();

/**
 * Check Affiliation Status and Capture Referral Code
 *
 * This checks if the affiliation feature is turned on.
 * If it is enabled, it captures the referral code from the URL or from the last cookie session.
 * This is useful for tracking and attributing referrals in the application.
 */
if(Uss::$global['options']->get('user:affiliation')) {
    Udash::get_sponsor();
}
