<?php
/**
 * The Sign-In Page
 *
 * Note: This file is included before the "template.php" file is called
 *
 * The easiest way to modify content of this page is to add or override `udash:auth.right` events
 */

defined('UDASH_DIR') or die;


/** Create the sign in form */

Events::addListener('udash:auth.right', function () { ?>

	<form method='post' action="%{udash.ajax}" id='auth-form' data-type='ud-signin' enctype='multipart/form-data'>
		<div class="row py-3">
			<div class="col-sm-10 col-md-9 m-auto">
				
				<?php Events::addListener('udash:auth/signin@form', function () { ?>
					<div class="mb-3">
						<input type="text" placeholder="Login detail" class='form-control' name='login' required pattern="^\s*(?:\w+|(?:[^@]+@[a-zA-Z0-9\-_]+\.\w{2,}))\s*$">
					</div>
				<?php }, EVENT_ID . 'field'); ?>
				
				
				<?php Events::addListener('udash:auth/signin@form', function () { ?>
					<div class="mb-4">
						<input type="password" placeholder="Password" class='form-control' name='password' required pattern='^.{4,}$'>
					</div>
				<?php }, EVENT_ID . 'field_100'); ?>
					
					
				<?php
                    Events::addListener('udash:auth/signin@form', function () {
                        /*
                            Remember Me: The option was removed!
                        */
                        ?>
					<div class='d-flex justify-content-between mb-3'>
						<div class='resend-email ms-auto'>
							<a href='javascript:void(0)' title="Resend Confirmation Email" data-vcode>
								<small>Reconfirm Email</small> <i class='bi bi-envelope-at'></i>
							</a>
						</div>
					</div>
				<?php }, EVENT_ID . 'field_200'); ?>
				
				
				<?php Events::addListener('udash:auth/signin@form', function () { ?>
					<div class="">
						<button class="btn btn-primary w-100" type='submit'>
							Sign In
						</button>
					</div>
				<?php }, EVENT_ID . 'field_300'); ?>
					
					
				<?php Events::exec('udash:auth/signin@form'); ?>
				
			</div>
		</div>
		<!-- end row -->
	</form>
	
<?php }, EVENT_ID . 'signin'); ?>
	
	
<?php Events::addListener('udash:auth.right', function () { ?>
	<div class="mt-2">
		<p class="text-sm text-center">
			Forgot Password? 
			<a href="%{udash.url}/reset" class="hover-underline text-nowrap">Reset Password</a>
		</p>
	</div>
<?php }, EVENT_ID . 'signin_100'); ?>


<?php
    Events::addListener('udash:auth.right', function () {
        $disabled = !empty(Uss::$global['options']->get('user:disable-signup'));
        if($disabled) {
            return;
        }
        ?>
	<div class="mt-2">
		<p class="text-sm text-center">
			Don’t have an account yet? 
			<a href="%{udash.url}/signup" class='text-nowrap'>Create an account</a>
		</p>
	</div>
<?php }, EVENT_ID . 'signin_200'); ?>
