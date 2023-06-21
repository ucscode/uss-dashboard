<?php

defined( 'UDASH_AJAX' ) OR DIE;

Events::addListener('@udash//ajax', function() {
	
	/**
	 * Test request nonce
	 */
	$trusted = Uss::nonce( $_SESSION['uss_session_id'], $_POST['nonce'] ?? null );
	
	if( !$trusted ) Uss::stop( false, 'The request is not from a trusted source' );
	
	/**
	 * Loop through $_POST recursively and sanitize all inputs
	 */
	
	array_walk_recursive($_POST, function(&$value, $key) {
		$value = Uss::$global['mysqli']->real_escape_string($value);
	});
	
	/**
	 * - Implode notification ID
	 * - Prepare table prefix
	 */
	$keys = implode( ",",  $_POST['nx'] );
	
	$prefix = DB_TABLE_PREFIX;

	/**
	 * Handle NX Request based on remark
	 */
	
	switch( $_POST['remark'] ) {
		
		/**
		 * Mark NX as viewed
		 */
		case 'viewed':
				$SQL = sQuery::update( "{$prefix}_notifications", array( "viewed" => 1 ), "id IN({$keys})" );
			break;
		
		/**
		 * Hide Notification
		 */
		case 'remove':
				$SQL = sQuery::update( "{$prefix}_notifications", array( 'hidden' => 1 ), "id IN({$keys})" );
			break;
			
	};


	$status = !empty($SQL) ? Uss::$global['mysqli']->query( $SQL ) : false;

	Uss::stop( $status );

}, 'ajax-nx' );

