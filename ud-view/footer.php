<?php

defined('UDASH_DIR') or die;

/**
 * Blank Page Test
 * This file is a continuation of the `header.php` file
 */

if(!self::$config['blank']):
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
Events::exec('udash:footer');
?>
	
</main>
