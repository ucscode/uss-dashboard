<?php 

defined( 'UDASH_MOD_DIR' ) OR DIE;

// Declare The Focus Path For Account Page

$profileFocus = UDASH_FOCUS_URI . "/account";


// Create Profile Menu

$account = uss::$global['menu']->add('profile', array(
	'label' => "Account",
	"icon" => "<i class='bi bi-person'></i>",
	"active" => uss::query(1) === 'account'
));


// Create Profile Child Menu

$account->add('profile', array(
	"label" => "Profile",
	"href" => core::url( ROOT_DIR . "/{$profileFocus}" ),
	"active" => implode("/", uss::query()) === $profileFocus
));


/**
 * Push a new item to the "userdrop" dropdown 
 * The "userdrop" is located at the TOP-RIGHT of the dashboard
 */

events::addListener('@auth//header//userdrop', function() use( $account ) { ?>
	<li>
		<a href="<?php echo $account->get('profile')->get_attr('href'); ?>">
			<i class="bi bi-person"></i> View Profile
		</a>
	</li>
<?php }, 'Profile');


/**
 * Focus on the profile path
 * 
 * The code below will run only when the URL matches the `$profileFocus`
 * @see \uss::focus
 */
uss::focus( $profileFocus, function( $e ) {
	
	/**
	 * CREATE NONCE KEY
	 * This is to prevent submission from unreliable source
	 */
	$nonce = uss::nonce( 'profile' );
	
	uss::eTag( 'nonce', $nonce );
	
		
	/**
	 * HANDLE POST REQUEST
	 */
	require __DIR__ . '/POST/profile.php';
	
	
	/**
	 * DISPLAY PROFILE CONTENT
	 */
	udash::view(function() use( $nonce ) { 
	
		/**
		 * Get Configuration Option: {lock-email}
		 */
		$lockemail = !empty(uss::$global['options']->get('user:lock-email'));
		
		/**
		 * Build Template Tags
		 *
		 * The template tags will enable us call on `%{tagname}` 
		 * Rather than re-writing the same PHP variables over again
		 */
		foreach( uss::$global['user'] as $key => $value ) uss::eTag("user.{$key}", $value, false);
		
		/**
		 * Load additional tags
		 * @param 3 == false; Tag is editable
		 */
		uss::eTag('user.title', uss::$global['user']['username'] ?: 'Hi dear', false);
		uss::eTag('user.avatar', udash::user_avatar( uss::$global['user']['id'] ), false);
		
		uss::eTag('profile.col-left', 'col-lg-5', false);
		uss::eTag('profile.col-right', 'col-lg-7', false);
		
	?>
		
		<section class="section %{profile.class}">
			<div class="container-fluid">
				<div class="row">
					
					<?php 
						/** 
						 * DISPLAY AFFILIATION LINK 
						 */
						events::addListener('@udash//page//profile', function() {
							
							if( !uss::$global['options']->get('user:affiliation') ) return;
							
					?>
						<div class='col-12 mb-3'>
							<div class='row flex-wrap align-items-center justify-content-end'>
								<div class='col-sm-4 col-md-6 mb-1'>
									<p class='fs-16px text-sm-end mb-0 fw-light'>
										<span class='text-primary'>Affiliate Link</span> 
										&mdash; <i class='bi bi-people'></i>
									</p>
								</div>
								<div class='col-sm-8 col-md-6'>
									<div class='input-group'>
										<input type='text' class='form-control form-control-lg text-sm' readonly value='%{udash.url}/signup?ref=%{user.usercode}' id='reflink'>
										<button class='btn btn-info' data-uss-copy='#reflink'>
											<i class='bi bi-clipboard'></i>
										</button>
									</div>
								</div>
							</div>
						</div>
					<?php }, EVENT_ID . 'affiliate-link' ); 
						
						/**
						 * Include Account Template:
						 *
						 * - Profile Form
						 * - Password Form
						 */
						require_once __DIR__ . '/SECTIONS/profile-forms.php';
						
						/**
						 * Execute the profile page events
						 */
						events::exec('@udash//page//profile'); 
						
					?>
					
				</div> <!-- end row -->
			</div> <!-- end container -->
		</section>
		
	<?php }); // udash::view
	
}, NULL); // uss::focus