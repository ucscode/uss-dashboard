<?php

defined('UDASH_DIR') or die;

Uss::tag('reset.type', !$reset ? 'ud-reset' : 'ud-reset-v2');


/** Create a reset password form */

Events::addListener('udash:auth.right', function () use ($reset) { ?>
	
	<form method='post' action="%{udash.ajax}" id='auth-form' data-type='%{reset.type}' enctype='multipart/form-data'>
		<div class="row py-3">
			<div class="col-sm-10 col-md-9 m-auto">
				
				<?php
                    if(empty($reset)):

                        /**
                         * Display a reset password form
                         * The form contains only an email field
                         * After submitting the form, the system checks for account existence and send a password
                         * reset email to the associated user
                         */
                        ?>
				
					
					<?php Events::addListener('udash:auth/reset@form', function () { ?>
						<div class='text-center mb-3'>
							<small>You'll receive an email to continue the process</small>
						</div>
					<?php }, EVENT_ID . 'field'); ?>
					
					
					<?php Events::addListener('udash:auth/reset@form', function () { ?>
						<div class="mb-3">
							<input type="email" name='email' placeholder="Email" class='form-control' required>
						</div>
					<?php }, EVENT_ID . 'field_100'); ?>
					
					
					<?php Events::addListener('udash:auth/reset@form', function () { ?>
						<button class="btn btn-primary w-100">
							Reset Password
						</button>
					<?php }, EVENT_ID . 'field_200'); ?>
				
				
				<?php
                    else:

                        /**
                         * After filling the form above, a password reset link will be sent to the user
                         * If user clicks the link, a password check will be carried out
                         * The context below will then display if the reset code is approved
                         */
                        ?>
				
					<?php Events::addListener('udash:auth/reset@form', function () { ?>
						<div class='text-center mb-3'>
							<small>Please enter your new password</small>
						</div>
					<?php }, EVENT_ID . 'field'); ?>
					
					
					<?php Events::addListener('udash:auth/reset@form', function () use ($reset) { ?>
					
						<div class="mb-3">
							<input type="password" name='password' placeholder="Password" class='form-control' pattern='^.{4,}$' required>
						</div>
						
						<div class="mb-3">
							<input type="password" name='confirm_password' placeholder="Confirm Password" class='form-control' pattern='^.{4,}$' required>
						</div>
						
						<input type='hidden' name='passport' value='<?php echo $reset; ?>'>
						<input type='hidden' name='nonce' value='<?php echo Uss::nonce($_SESSION['resetter']); ?>'>
						
					<?php }, EVENT_ID . 'field_100'); ?>
					
					<?php Events::addListener('udash:auth/reset@form', function () { ?>
						<button class="btn btn-primary w-100">
							Change Password
						</button>
					<?php }, EVENT_ID . 'field_200'); ?>
				
				<?php endif; ?>
				
				<?php Events::exec('udash:auth/reset@form', [$reset]); ?>
				
			</div>
		</div>
		<!-- end row -->
	</form>

<?php }, EVENT_ID . 'reset'); ?>


<?php Events::addListener('udash:auth.right', function () { ?>
	<div class="mt-4">
		<p class="text-sm text-medium text-dark text-center">
			Back To <a href="%{udash.url}">Login</a>
		</p>
	</div>
<?php }, EVENT_ID . 'reset_100'); ?>

