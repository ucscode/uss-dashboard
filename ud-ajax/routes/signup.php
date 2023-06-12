<?php 

defined( "UDASH_AJAX" ) OR DIE;

# ------------------------------------------

events::addListener('@udash//ajax', function() {
	
	/**
	 * Define a redirect URL
	 * The URL that user will be redirected to after a successful registration
	 */
	 
	$redirect = udash::config('signup:redirect') ?? core::url( ROOT_DIR . '/' . UDASH_FOCUS_URI );
	
	
	/**
	 * Trim all character
	 */
	
	$_POST = array_map('trim', $_POST);
	
	
	/**
	 * The table prefix
	 */
	$prefix = DB_TABLE_PREFIX;
	
	
	/**
	 * Check if the email address is valid
	 */
	
	if( !preg_match( core::regex('email'), $_POST['email'] ) ) $message = "The email address is not valid";
		
		
	/**
	 * Check if username is valid
	 */
	
	elseif( isset($_POST['username']) && !preg_match("/^\w+$/i", $_POST['username']) ) 
	
		$message = "Username can only contain number, letter and underscore";
	
	
	/**
	 * Check if password matches;
	 */
	
	elseif ( $_POST['password'] !== $_POST['confirm_password'] ) $message = "Password does not match";

	
	/**
	 * All conditions are met
	 */
	else {
		
		/**
		 * Check if the email address already exists;
		 */
		
		$user = udash::fetch_assoc( "{$prefix}_users", $_POST['email'], 'email' );
		
		/**
		 * If a user if found
		 * Then, the email exists
		 */
		 
		if( $user ) $message = "The email already exists";
			
		else {
			
			/**
			 * Check if the username already exists;
			 */
			
			$username = $_POST['username'] ?? null;
			$user = udash::fetch_assoc( "{$prefix}_users", $username, 'username' );
			
			/**
			 * If username is set &
			 * A user is found
			 * Then, the username exists
			 */
			if( !empty($username) && $user ) $message = "The username already exists";
				
			else {
				
				/*
					If parent ID is supplied:
					Then: confirm that the parent exists!
				*/
				
				if( isset($_POST['parent']) && !empty(uss::$global['options']->get('user:affiliation')) ) {
					$parent = udash::fetch_assoc("{$prefix}_users", $_POST['parent']);
					if( $parent ) $parent = $parent['id'];
				} else $parent = null;
				
				/**
				 * Great! 
				 * All validation process has been confirmed!
				 */
				
				$data = array(
					"email" => $_POST['email'],
					"password" => udash::password( $_POST['password'] ),
					"username" => $username,
					"usercode" => core::keygen(mt_rand(5,7)),
					"parent" => $parent
				);
				
				/**
				 * Insert into database
				 * sQuery will auto sanitize the input
				 */
				
				$SQL = sQuery::insert( "{$prefix}_users", $data, uss::$global['mysqli'] );
				
				/**
				 * Insert the user into database
				 * Then get the userid immediately
				 */
				$status = uss::$global['mysqli']->query( $SQL );
				
				$data['id'] = uss::$global['mysqli']->insert_id;
				
				
				/** On success */
				
				if( $status ) {
					
					/**
					 * Assign a role to the new user!
					 */
					
					$defaultRole = uss::$global['options']->get('user:default-role');
					
					$assigned = roles::user( $data['id'] )::assign( $defaultRole );
					
					/**
					 * Clear Access Token!
					 */
					udash::setAccessToken( null );
					
					/**
					 * The success message
					 */
					$message = "<i class='bi bi-check-circle text-success me-1'></i> Your registration was successful";
					
					
					/**
					 * Send verification email
					*/
					
					$verify_mail = !empty( uss::$global['options']->get('user:confirm-email') );
					
					if( $verify_mail ) {
						
						/**
						 * Send a new confirmation link to the user
						 * The user will be able to login on after verifying the link
						 */
						$sent = udash::send_confirmation_email( $data['email'] );
						
						$className = 'mt-3 pt-3 border-top fs-14px';
						
						if( $sent ) {
							
							$message .= "
								<div class='{$className} text-success'> 
									Please confirm the link sent to your email
								</div>
							";
							
						} else {
							
							$message .= "
								<div class='{$className} text-danger'> 
									Email confirmation link failed to send. <br> Try requesting for a new link in the login form
								</div>
							";
						
						};
						
					};
				
					# ------- [ End Email Verification ] -----------
					
				} else {
					
					/**
					 * If user detail was not inserted into database
					 * Output a failure message
					 */
					$message = "<i class='bi bi-x-circle text-danger me-1'></i> The registration was not successful";
				
				}
				
			} // username doesn't exits
			
		} // email doesn't exist
		
	}; // all conditions are met
	
	/**
	 * Print the output and end the script
	*/
	
	uss::stop( $status ?? false, $message, array( 'redirect' => $redirect ) );
	
}, 'ajax-signup' );

