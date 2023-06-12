<?php 
/**
 * The auth form template
 *
 * ## How to modify:
 *
 * - Add listener to each events you intend to modify
 * - Update all template tags you want to modify
 * - Then include the file in your project
 *
 * #### Example:
 *
 * ```php
 *	
 * events::addListener('@auth//left', function() {
 *	// content to display at left side
 * });
 *	
 * // You can equally pass an id to override a content
 
 * events::addListener('@auth//right', function() {
 *	// content to display at right side
 * }, 'event-id');
 *
 * uss::eTag('auth.container', 'row');
 * uss::eTag('col.left', 'd-none');
 * uss::eTag('col.right', 'col-lg-12');
 *
 * require_once "/path/to/AUTH/template.php";
 *
 */
defined( 'UDASH_MOD_DIR' ) OR DIE;

?>
<div class="%{col.row}">
	
	<div class="%{col.left}">
		
		<?php events::addListener('@auth//left', function() { ?>
			
			<div class="auth-cover-wrapper bg-primary-100">
				<div class="auth-cover">
					<div class="title text-center">
						
						<!-- image -->
						<div class='mb-2'>
							<img src='<?php echo uss::$global['icon']; ?>' class='img-fluid user-select-none'>
						</div>
						
						<h1 class="text-white mb-10">
							<?php echo uss::$global['title']; ?>
						</h1>
						
						<p class="text-light">
							<?php echo uss::$global['tagline']; ?>
						</p>
						
					</div>
				</div>
			</div>
		
		<?php }, 0); ?>
		
		<?php events::exec('@auth//left'); ?>
		
	</div>

	<div class="%{col.right}">
	
		<div class="%{auth.container}">
			<div class="flex-grow-1">
			
				<?php events::exec('@auth//right'); ?>
				
			</div>
		</div>
		
	</div>

</div>
