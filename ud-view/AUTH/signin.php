<?php 
/**
 * The Sign-In Page
 *
 * Note: This file is included before the "template.php" file is called
 *
 * The easiest way to modify content of this page is to add or override `@auth//right` events
 */
 
defined( 'UDASH_MOD_DIR' ) OR DIE;


/** Create the sign in form */

events::addListener('@auth//right', function() { ?>

	<form method='post' action="%{udash.ajax}" id='auth-form' data-type='ud-signin' enctype='multipart/form-data'>
		<div class="row py-3">
			<div class="col-sm-10 col-md-9 m-auto">
				
				<?php events::addListener('@auth//form//signin', function() { ?>
					<div class="mb-3">
						<input type="text" placeholder="Login detail" class='form-control' name='login' required pattern="^\s*(?:\w+|(?:[^@]+@[a-zA-Z0-9\-_]+\.\w{2,}))\s*$">
					</div>
				<?php }, EVENT_ID . 'login'); ?>
				
				
				<?php events::addListener('@auth//form//signin', function() { ?>
					<div class="mb-4">
						<input type="password" placeholder="Password" class='form-control' name='password' required pattern='^.{4,}$'>
					</div>
				<?php }, EVENT_ID . 'password'); ?>
					
					
				<?php 
					events::addListener('@auth//form//signin', function() { 
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
				<?php }, EVENT_ID . 'reconfirm'); ?>
				
				
				<?php events::addListener('@auth//form//signin', function() { ?>
					<div class="">
						<button class="btn btn-primary w-100" type='submit'>
							Sign In
						</button>
					</div>
				<?php }, EVENT_ID . 'submit'); ?>
					
					
				<?php events::exec('@auth//form//signin'); ?>
				
			</div>
		</div>
		<!-- end row -->
	</form>
	
<?php }, EVENT_ID . 'signin-form'); ?>
	
	
<?php events::addListener('@auth//right', function() { ?>
	<div class="mt-2">
		<p class="text-sm text-center">
			Forgot Password? 
			<a href="%{udash.url}/reset" class="hover-underline text-nowrap">Reset Password</a>
		</p>
	</div>
<?php }, EVENT_ID . 'signin-reset'); ?>


<?php 
	events::addListener('@auth//right', function() { 
		$disabled = !empty(uss::$global['options']->get('user:disable-signup'));
		if( $disabled ) return;
?>
	<div class="mt-2">
		<p class="text-sm text-center">
			Don’t have an account yet? 
			<a href="%{udash.url}/signup" class='text-nowrap'>Create an account</a>
		</p>
	</div>
<?php }, EVENT_ID . 'signin-reverse'); ?>
