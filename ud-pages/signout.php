<?php 

defined( 'UDASH_MOD_DIR' ) OR DIE;

// ----------------- [{ signout }] --------------------

Uss::route( Udash::config('page:signout'), function() {
	
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
