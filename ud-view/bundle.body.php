<?php 

/**
 * Body Elements
 * This file container all default tags that should be within the `<body></body>` section
 */
 
defined( 'UDASH_MOD_DIR' ) OR DIE; 

?>	<script src="<?php echo Core::url( udash::ASSETS_DIR . "/js/polyfill.js" ); ?>"></script>
<?php if( !udash::config('auth') ): ?>
	<script src="<?php echo Core::url( udash::ASSETS_DIR . "/js/access.js" ); ?>"></script>
<?php else: ?>
	<script src="<?php echo Core::url( udash::ASSETS_DIR . "/js/dashboard.js" ); ?>"></script>
<?php endif; ?>