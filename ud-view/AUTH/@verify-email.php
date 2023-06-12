<?php 

defined( 'UDASH_MOD_DIR' ) OR DIE;

/*

	This file was required in UDASH_MOD_DIR . "/model/class.php";
	
	That is where this closure is called!
	
*/

call_user_func(function() {
	
	
	if( !isset( $_GET['v'] ) ) return;
	
	try {
		
		/* 
			Decode & Validate The String;
		*/
		
		$data = explode( ":", base64_decode( $_GET['v'] ) );
		
		if( count($data) < 3 ) return uss::console( '@alert', '"Invalid confirmation link"' );
		
		
		/*
			Get the user by email;
		*/
		
		$user = udash::fetch_assoc( DB_TABLE_PREFIX . "_users", $data[2], 'email' );
		
		if( !$user ) return uss::console( '@alert', "Confirmation link is forbidden" );
		
		
		/*
			Get the epoch and confirm expiry time!
		*/
		
		$v_key = ( $data[0] === 'update' ) ? 'v-code:update' : 'v-code';
		
		$vcode = uss::$global['usermeta']->get( $v_key, $user['id'] );
		
		if( !$vcode ) return;
		
		
		/*
			Capture the epoch!
			
		*/
		
		$epoch = uss::$global['usermeta']->get( $v_key, $user['id'], true );

		// check expired link;
		
		$time = (new DateTime())->setTimestamp( $epoch );
		$now = new DateTime();
		
		if( $now->diff( $time )->h > 12 ) return uss::console( '@alert', "Confirmation link expired" );
		
		
		/*
			Get the verification code;
		*/
		
		if( $vcode !== $data[1] ) return uss::console( '@alert', "Email confirmation failed <br> Please try again" );
		
		
		/*
			Confirm the Email Address;
		*/
		
		if( $v_key == 'v-code' ) {
			
			$confirmed = uss::$global['usermeta']->remove( 'v-code', $user['id'] ); 
		
		} else {
			
			/*
				Get the new email address!
			*/
			
			$email = uss::$global['usermeta']->get( 'v-code:email', $user['id'] );
			
			// Test for validity!
			
			if( preg_match( core::regex('email'), $email ) ) {
				
				$prefix = DB_TABLE_PREFIX;
				
				$SQL = sQuery::update( "{$prefix}_users", array(
					"email" => $email
				), "id = '{$user['id']}'", uss::$global['mysqli'] );
				
				// insert the new email address!
				
				$confirmed = uss::$global['mysqli']->query( $SQL );
				
				/*
					Remove the usermeta key as it has become irrelevant!
				*/
				
				foreach( ['v-code:update', 'v-code:email'] as $key )
					uss::$global['usermeta']->remove( $key, $user['id'] );
				
			} else return uss::console( '@alert', "The email to update cannot be retreived!" );
			
		};
		
		if( $confirmed ) {
			uss::console( '@alert', "Your email address has been confirmed" );
		} else uss::console( '@alert', "Your email address could not be confirmed <br> Try contacting the support team" );
	
	
	} catch( Exception $e ) {
		
		// Output error message;
		
		uss::console( '@alert', "An unexpected error occured when trying to confirm the email <br> Try contacting the support team" );
	
	};
	
});