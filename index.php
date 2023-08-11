<?php
/**
 * Dashboard Module for User Synthetics
 *
 * The user synthetics dashboard module enables developer to build quick user-based project without need of re-creating a user management system.
 * uss dashboard comes with beautiful and friendly user interface that is easily managable and customizable.
 * It is backed by powerful event driven API to fit the need of any project and also allows modification of the system by other modules.
 *
 * `ud` &mdash; represents `uss` `dashboard`
 *
 * @version 2.3.0
 * @author ucscode <uche23mail@gmail.com>
 * @link https://github.com/ucscode
 * @copyright Copyright (c) 2023
 * @package Uss\Dashboard
 */

defined('ROOT_DIR') or die;

/**
 * uss dashboard module directory
 * The directory (folder) of the dashboard module
 */
define("UDASH_DIR", __DIR__);

/**
 * uss dashboard focus URI
 * The focus URI (i.e url path) that will be entered in the browser to access the dashboard module.
 */
define("UDASH_ROUTE", 'dashboard');

/**
 * The uss dashboard module requires database connection to work properly
 * Check if database connect is allowed
 */
if(DB_CONNECT) {

    /**
     * uss dashboard resources
     * This are classes and utilities that powers and helps simplify the usage of the module
     */
    $resources = array(
        "db.php",
        "UdashAbstract.php", #abstract
        "Udash.php",
        "DOMTableWidget.php", #abstract
        "DOMTablet.php",
        "Roles.php",
        "Hierarchy.php",
        "phpmailer.php"
    );

    # require all dashboard resources

    foreach($resources as $file) {
        require_once UDASH_DIR . "/ud-resources/{$file}";
    }


    /**
     * Now that all dashboard resources has been loaded
     * Let's Initialize the dashboard
     */
    require UDASH_DIR . '/ud-init.php';


    /**
     * Create User Interface Pages
     *
     * This code block is responsible for creating the default pages of the User Synthetics (uss) dashboard.
     * The pages that are created include:
     * - Profile page
     * - Affiliate page
     * - Hierarchy page
     *
     * These pages provide users with a user-friendly interface to manage their profile, affiliate information, and hierarchy.
     *
     * The pages will be loaded only when the dashboard URI is accessed
     * @see \udash_abstract::load()
     */
    Udash::load(UDASH_ROUTE, UDASH_DIR . '/ud-interface.php');


    /**
     * Integration with User Synthetics Dashboard
     *
     * The User Synthetics (uss) dashboard interface is rendered after all modules are loaded,
     * allowing any existing module to seamlessly add new features and enhance the dashboard's functionality.
     * To ensure proper integration with the uss dashboard, modules should listen to the `udash:ready` event.
     * By adding a listener to this event, modules can perform necessary setup tasks and inject their custom features
     * into the dashboard, ensuring a smooth and cohesive user experience.
     */
    Events::addListener('modules:loaded', function () {

        /**
         * Finalizing User Synthetics Dashboard
         *
         * Once other modules have successfully integrated with the uss dashboard,
         * the uss dashboard module takes the final step before rendering the output.
         * This step involves performing any necessary finalization tasks, ensuring all components are properly configured
         * and ready to be displayed to the user. This finalization step is crucial for delivering a polished and seamless
         * user experience within the uss dashboard.
         */
        Events::addListener('udash:ready', function() {
            
            # Process last event

            require UDASH_DIR . '/ud-final.php';

        }, 10000);
        

        # Execute all dashboard events;

        Events::exec('udash:ready');

        /**
         * Avoid adding a listener to the `//udash//view` event
         *
         * Adding a listener to the `//udash//view` event is not recommended.
         * The `//udash//view` event is a system-level event used internally by the User Synthetics Dashboard module.
         * Listening to this event directly will not propably not render anything

         * The module is only intended to be used by the `Udash::view()` method to render final output after all other module has
         * been integrated into the dashboard module
         */
        Events::exec('//udash//view', null, null, function ($debug) {
            return ($debug[0]['file'] === (new ReflectionClass('udash'))->getFilename());
        });

    });

} else { // If database connection is disabled

    /**
     * Restrict Access to Dashboard
     * If the dashboard path is accessed, display a notice to user
     */
    Uss::route(UDASH_ROUTE, function () {

        /**
         * Prepare the user synthetics interface
         */
        Uss::view(function () {

            /**
             * Display the notification content;
             */
            echo "
				<div class='container-fluid'>
					<div class='text-center p-4 row'>
						<div class='col-lg-6 mx-auto'>
							<h4><u>DATABASE CONNECTION IS DISABLED</u></h4>
							<div class='py-4 px-3 border my-4 rounded-2 lead shadow-sm'>
								Please update your database connection to use dashboard module! 
								<div class='my-3 border-top'></div>
								<strong>DB_CONNECT</strong> MUST BE SET TO <span class='text-info fw-500'>TRUE</span>
							</div>
						</div>
					</div>
				</div>
			";

        });

    }); // end focus

};
