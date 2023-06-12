<?php 

defined( 'UDASH_MOD_DIR' ) OR DIE; 

/**
 * Blank Page Test
 * This file is a continuation of the `header.php` file
 */

	if( !self::$config['blank'] ): 
?>

	</div> <!-- === [ //content-wrapper ] === -->
	
	<?php 
		/**
		 * // Blank Page Test
		 */
		endif; 
		
		/**
		 * Execute Footer Event
		 */
		events::exec('@udash//footer');
	?>
	
</main>
