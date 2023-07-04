<?php

/**
 * Body Elements
 * This file container all default tags that should be within the `<body></body>` section
 */

defined('UDASH_DIR') or die;

?>	<script src="<?php echo Core::url(Udash::ASSETS_DIR . "/js/polyfill.js"); ?>"></script>
<?php if(!Udash::config('auth')): ?>
	<script src="<?php echo Core::url(Udash::ASSETS_DIR . "/js/access.js"); ?>"></script>
<?php else: ?>
	<script src="<?php echo Core::url(Udash::ASSETS_DIR . "/js/dashboard.js"); ?>"></script>
<?php endif; ?>