<?php

defined('UDASH_DIR') or die;

/** Create signup form */

Events::addListener('udash:auth.right', function () { ?>

	<form method='post' action="%{udash.ajax}" id='auth-form' data-type='ud-signup' enctype='multipart/form-data'>
		<div class="row py-3">
			<div class="col-sm-10 col-md-9 m-auto">
				
				<?php
                    Events::addListener('udash:auth/signup@form', function () {
                        /**
                         * Check if username collection is disabled
                         */
                        if(empty(Uss::$global['options']->get("user:collect-username"))) {
                            return;
                        }
                ?>
					<div class="mb-3">
						<input type="text" placeholder="Username" class='form-control' name='username' required pattern="^\s*\w+\s*$">
					</div>
				<?php }, EVENT_ID . 'field'); ?>
				
				
				<?php
                    Events::addListener('udash:auth/signup@form', function () { 
				?>
					<div class="mb-3">
						<input type="email" placeholder="Email" class='form-control' name='email' required>
					</div>
				<?php }, EVENT_ID . 'field_100'); ?>
				
				
				<?php
                    Events::addListener('udash:auth/signup@form', function () { 
				?>
					<!-- end col -->
					<div class="mb-3">
						<input type="password" placeholder="Password" class='form-control' name='password' required pattern='^.{4,}$'>
					</div>
				<?php }, EVENT_ID . 'field_200'); ?>
				
				
				<?php
                    Events::addListener('udash:auth/signup@form', function () { 
				?>
					<!-- end col -->
					<div class="mb-4">
						<input type="password" placeholder="Confirm Password" class='form-control' name='confirm_password' pattern='^.{4,}$' required>
					</div>
				<?php }, EVENT_ID . 'field_300'); ?>
				
				
				<?php
                    Events::addListener('udash:auth/signup@form', function () { 
				?>
					<!-- end col -->
					<div class='mb-4'>
						<div class="form-check user-select-none">
							<input class="form-check-input" type="checkbox" value="" id="tos" required>
							<label class="form-check-label" for="tos">
								<small>
									I agree to the <a href='<?php echo Udash::config('tos-page') ?? 'javascript:void(0)'; ?>'>Terms Of Service</a> &amp; <a href='<?php echo Udash::config('privacy-page') ?? 'javascript:void(0)'; ?>'>Privacy Policy</a>
								</small>
							</label>
						</div>
					</div>
				<?php }, EVENT_ID . 'field_400'); ?>
				
				
				<?php
                    Events::addListener('udash:auth/signup@form', function () {
						if(empty(Uss::$global['options']->get('user:affiliation'))) {
							return;
						}
						$parent = Udash::get_sponsor();
						if(!$parent) {
							return;
						}
					?>
						<input type='hidden' name='parent' value='<?php echo $parent['id']; ?>'>
				<?php }, EVENT_ID . 'field_500'); ?>
				
				
				<?php
                    Events::addListener('udash:auth/signup@form', function () { 
				?>
					<!-- end col -->
					<div class="button-group d-flex justify-content-center flex-wrap">
						<button class="btn btn-primary w-100" type='submit'>
							Sign Up
						</button>
					</div>
				<?php }, EVENT_ID . 'field_600'); ?>
				
				
				<?php Events::exec('udash:auth/signup@form'); ?>
				
			</div>
		</div>
	<!-- end row -->
	</form>
	
<?php }, EVENT_ID . 'signup'); ?>


<?php
    Events::addListener('udash:auth.right', function () { ?>
		<div class='mt-4'>
			<p class="text-sm text-medium text-dark text-center">
				Already have an account? 
				<a href="%{udash.url}" class='text-nowrap'>
					Sign In
				</a>
			</p>
		</div>
	<?php }, EVENT_ID . 'signup_100');
?>


