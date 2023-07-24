<?php

defined('UDASH_DIR') or die;

// Declare The Focus Path For Account Page

$profileFocus = UDASH_ROUTE . "/account";


// Create Profile Menu

$account = Uss::$global['menu']->add('profile', array(
    'label' => "Account",
    "icon" => "<i class='bi bi-person'></i>",
    "order" => 1
));


// Create Profile Child Menu

$profileMenu = $account->add('profile', array(
    "label" => "Profile",
    "href" => Core::url(ROOT_DIR . "/{$profileFocus}")
));


/**
 * Push a new item to the "userdrop" dropdown
 * The "userdrop" is located at the TOP-RIGHT of the dashboard
 */

Events::addListener('udash:pages/account@header.userdrop', function () use ($account) { ?>
	<li>
		<a href="<?php echo $account->get('profile')->getAttr('href'); ?>">
			<i class="bi bi-person"></i> View Profile
		</a>
	</li>
<?php }, EVENT_ID );


/**
 * Focus on the profile path
 *
 * The code below will run only when the URL matches the `$profileFocus`
 * @see \Uss::route
 */
Uss::route($profileFocus, function ($e) use($profileMenu) {

    $profileMenu->setAttr('active', true);
    $profileMenu->parentMenu->setAttr('active', true);
    
    /**
     * CREATE NONCE KEY
     * This is to prevent submission from unreliable source
     */
    $nonce = Uss::nonce('profile');

    Uss::tag('nonce', $nonce);


    /**
     * HANDLE POST REQUEST
     */
    require __DIR__ . '/POST/profile.php';


    /**
     * DISPLAY PROFILE CONTENT
     */
    Udash::view(function () use ($nonce) {

        /**
         * Get Configuration Option: {lock-email}
         */
        $lockemail = !empty(Uss::$global['options']->get('user:lock-email'));

        /**
         * Build Template Tags
         *
         * The template tags will enable us call on `%{tagname}`
         * Rather than re-writing the same PHP variables over again
         */
        foreach(Uss::$global['user'] as $key => $value) {
            Uss::tag("user.{$key}", $value, false);
        }

        /**
         * Load additional tags
         * @param 3 == false; Tag is editable
         */
        Uss::tag('user.title', Uss::$global['user']['username'] ?: 'Hi dear', false);
        Uss::tag('user.avatar', Udash::user_avatar(Uss::$global['user']['id']), false);

        Uss::tag('profile.col-left', 'col-lg-5', false);
        Uss::tag('profile.col-right', 'col-lg-7', false);

        ?>
		
		<section class="section %{profile.class}">
			<div class="container-fluid">
				<div class="row">
					
					<?php
                            /**
                             * DISPLAY AFFILIATION LINK
                             */
                            Events::addListener('udash:pages/profile', function () {

                                if(!Uss::$global['options']->get('user:affiliation')) {
                                    return;
                                }

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
					<?php }, EVENT_ID . 'affiliate_link');

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
                    Events::exec('udash:pages/profile');

        ?>
					
				</div> <!-- end row -->
			</div> <!-- end container -->
		</section>
		
	<?php }); // Udash::view

}, null); // Uss::route
