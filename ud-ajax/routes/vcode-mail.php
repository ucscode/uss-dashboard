<?php 

defined( 'UDASH_AJAX' ) OR DIE;


events::addListener('@udash//ajax', function() {
	
	/**
	 * Get the user by email
	 */
	$user = udash::fetch_assoc( DB_TABLE_PREFIX . "_users", $_POST['email'], 'email' );
	
	/** If no user is found, end the script */
	
	if( !$user ) uss::stop( false, "No account is associated to the email" );


	/**
	 * Check if the email has already been verified!
	 * If a `v-code` key does not exist on the user meta,
	 * It means the email is verified
	 */
	$vcode = uss::$global['usermeta']->get('v-code', $user['id']);
	
	/** If email is verified, end the script */
	
	if( !$vcode ) uss::stop( false, "The email address has already been confirmed" );


	/** 
	 * Resend The confirmation email!
	 * This will update the `v-code` key with a new one
	 * Any previous email sent becomes invalid
	 */
	$sent = udash::send_confirmation_email( $user['email'] );
	
	/**
	 * Get the response message
	 */
	$message = $sent ? 'Please confirm the link sent to your email' : 'Sorry! email confirmation link could not be sent';


	/**
	 * Inform the client
	 * Then end the script
	 */

	uss::stop( $sent, $message );

}, 'ajax-vcode' );