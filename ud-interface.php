<?php 

defined( 'UDASH_MOD_DIR' ) OR DIE;

/**
 * Focus on the dashboard path
 *
 * Include every file that is required to build the dashboard interface
 * Note: The `udash::ready` event can be overridden by modules that need custom functionality. 
 * For example: If you do not want the dashboard interface to exist and would rather prefer to create yours.
 *
 * Attaching a listener to `udash::ready` event is the appropriate way to modify content on uss dashboard
 * Do not try to modify dashboard content without using the `udash::ready` event
 * as it is possible that your code will run even before the dashboard module is loaded
 *
 * In other case, your code may be called after the dashboard has loaded but will not be properly handled
 * Hence, it highly recommended to bind your dashboard function to the `udash::ready` event
 */
events::addListener('udash::ready', function() {
	
	require udash::PAGES_DIR . '/index.php'; # visible immediately after login
	require udash::PAGES_DIR . '/account.php';
	require udash::PAGES_DIR . '/affiliates.php';
	require udash::PAGES_DIR . '/hierarchy.php';
	
	/**
	 * Focus expression for authentication pages
	 * The expression would focus on "/signup" and "/reset" page of the dashboard
	 *
	 * Note: Login page is solely handled by the `udash::view()`
	 * It take account of the dashboard configuration setting and the user authentication before determining 
	 * whether to display the actual content or the login page
	 */
	uss::focus( UDASH_FOCUS_URI . '/(?:signup|reset)', function() {
		
		/**
		 * Forcefully disable authentication for signup and reset password page
		 *
		 * Note that disabling a dashboard authentication does not logout the user
		 * It only emulates the logout effect and take user away from the dashboard panel
		 *
		 * To add a custom authentication page through module, add a callable to the `auth-page` offset
		 * ```php
		 * 	udash::config('auth-page', function() {
		 * 		if( uss::query(1) == 'signup' ) // your custom signup content here 
		 *  });
		 * ```
		 */
		udash::config('auth', false);
		
		# display the content;
		udash::view(function(){});
		
	});
	

}, EVENT_ID );


/**
 * Authenticate User
 *
 * User must be logged-in (with a role that has permission to view the dashboard)
 * The code below will run immediately since it's not attached to an event
 */
$user = uss::$global['user'];

/** 
 * If user is logged-in
 */
if( $user )  {
	
	$permission = roles::user( $user['id'] )::hasPermission( 'view-dashboard' );
	
	/**
	 * But does not have permission to view the current panel
	 */
	if( !$permission ) {
		
		/**
		 * Render a 403 Error Page
		 */
		uss::view(function() {
			require VIEW_DIR . "/error-403.php";
		});
		
	} else udash::config( 'auth', $permission ); // Approve permission
	
};
