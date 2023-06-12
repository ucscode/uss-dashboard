<?php 

defined( 'UDASH_MOD_DIR' ) OR DIE;

call_user_func(function() use($profileFocus) {
	
	/**
	 * Check if affiliation is turned off
	 * If it is, ignore this functionality
	 */
	if( empty(uss::$global['options']->get('user:affiliation')) ) return;
	
	// The Focus Expression
	$hierFocus = $profileFocus . "/hierarchy";
	
	/**
	 * Create a menu to access the page
	 */
	uss::$global['menu']->get('profile')->add('team-tree', array(
		'label' => "Hierarchy",
		'href' => core::url( ROOT_DIR . "/{$hierFocus}" ),
		"active" => $_GET[ uss::QueryKey ] == $hierFocus
	));
	
	/**
	 * Create a focus
	 * The URI required to view the content
	 * `(?:/?\w+)?` expression accepts extra query string containing usercode
	 * This will enable you view hierarchy of a particular downlines
	 */
	uss::focus( $hierFocus . "(?:/?\w+)?", function() use($hierFocus) {
		
		events::addListener('@head::after', function() {
			/**
			 * Get CSS that will style the nodes
			 */
			$dir = core::url( udash::ASSETS_DIR . "/vendor/datatree" );
			echo "\t<link rel='stylesheet' href='{$dir}/treeNode.css'/>\n";
		});
		
		events::addListener('@body::after', function() use($hierFocus) {
			
			/**
			 * Get JavaScript the will render the nodes
			 */
			$dir = core::url( udash::ASSETS_DIR . "/vendor/datatree" );
			echo "\t<script src='{$dir}/treeData.js'></script>\n";
			
			/**
			 * Require the PHP file that will build the node
			 * And then render it
			 */
			require_once __DIR__ . '/SECTIONS/treedata.php';
		});
		
		/**
		 * Display the hierarchy tree
		 */
		udash::view(function() { 
			/**
			 * Create Event
			 */
			events::addListener('@udash//page//hierarchy', function() {
		?>
			<div class='container-fluid'>
				<div class='tree-container position-relative rounded-2'>
					<div id='hierarchy' class='overflow-auto'></div>
				</div>
			</div>
		<?php }, EVENT_ID . 'hierarchy' ); // End Event
			
			/**
			 * Execute Events
			 */
			events::exec('@udash//page//hierarchy');
			
		}); // End view
		
	});
	
});