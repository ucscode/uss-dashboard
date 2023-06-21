<?php 

defined( 'UDASH_MOD_DIR' ) OR DIE;

// ----------------- [{ signout }] --------------------

Uss::focus( Udash::config('page:signout'), function() {
	
	/**
	 * Destroy Login Session;
	 */
	Udash::setAccessToken( null );
	
	/**
	 * Redirect page;
	 */
	header( "location: " . Udash::config('signout:redirect') );
	
	/** 
	 * EXIT THE SCRIPT 
	 */;
	exit();
	
});
