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
 * Events::addListener('auth:left', function() {
 *	// content to display at left side
 * });
 *
 * // You can equally pass an id to override a content

 * Events::addListener('auth:right', function() {
 *	// content to display at right side
 * }, 'event-id');
 *
 * Uss::tag('auth.container', 'row');
 * Uss::tag('col.left', 'd-none');
 * Uss::tag('col.right', 'col-lg-12');
 *
 * require_once "/path/to/AUTH/template.php";
 *
 */
defined('UDASH_DIR') or die;

?>
<div class="%{col.row}">
	
	<div class="%{col.left}">
		
		<?php Events::addListener('udash:auth.left', function () { ?>
			
			<div class="auth-cover-wrapper bg-primary-100">
				<div class="auth-cover">
					<div class="title text-center">
						
						<!-- image -->
						<div class='mb-2'>
							<img src='<?php echo Uss::$global['icon']; ?>' class='img-fluid user-select-none'>
						</div>
						
						<h1 class="text-white mb-10">
							<?php echo Uss::$global['title']; ?>
						</h1>
						
						<p class="text-light">
							<?php echo Uss::$global['tagline']; ?>
						</p>
						
					</div>
				</div>
			</div>
		
		<?php }, EVENT_ID . 'default'); ?>
		
		<?php Events::exec('udash:auth.left'); ?>
		
	</div>

	<div class="%{col.right}">
	
		<div class="%{auth.container}">
			<div class="flex-grow-1">
			
				<?php Events::exec('udash:auth.right'); ?>
				
			</div>
		</div>
		
	</div>

</div>
