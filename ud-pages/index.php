<?php 

defined( 'UDASH_MOD_DIR' ) OR DIE;

/**
 * Create Dashboard Menu
 * 
 * It is recommended to create menu outside a focus path
 * This will make it possible for users to access the menu outside the path
*/
uss::$global['menu']->add('homepage', array(
	'label' => "Dashboard",
	"icon" => "<i class='bi bi-speedometer2'></i>",
	'href' => core::url( ROOT_DIR . "/" . UDASH_FOCUS_URI ),
	'active' => implode("/", uss::query()) === UDASH_FOCUS_URI
));


// Focus Path;

uss::focus( UDASH_FOCUS_URI, function() {
	
	// Authenticate Email Requests
	
	require udash::VIEW_DIR . "/AUTH/@verify-email.php";
	
	udash::view(function() {
		
		/**
		 * The index page is empty
		 * A module needs to fill it up by adding an event listener;
		*/
		
		events::exec('@udash//page//index');
		
	});
	
});
