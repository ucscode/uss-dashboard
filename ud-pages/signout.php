<?php 

defined( 'UDASH_MOD_DIR' ) OR DIE;

// ----------------- [{ signout }] --------------------

uss::focus( udash::config('page:signout'), function() {
	
	/**
	 * Destroy Login Session;
	 */
	udash::setAccessToken( null );
	
	/**
	 * Redirect page;
	 */
	header( "location: " . udash::config('signout:redirect') );
	
	/** 
	 * EXIT THE SCRIPT 
	 */;
	exit();
	
});
