<?php

defined('UDASH_DIR') or die;

/**
 * DISPLAY ACCOUNT FORM
 */
Events::addListener('udash:pages/profile', function () {

    ?>
	<div class="%{profile.col-left}">
		<div class="card settings-card-1 mb-30">
			<div class='card-body'>
				
				<div class='p-3'>
					<div class="profile-info">
						<form method='POST' enctype='multipart/form-data'>
							<fieldset>

								<?php 
									Events::addListener('udash:pages/profile.left@form', function() {
									?>
								<div class="d-flex flex-wrap align-items-center mb-30">
									<div class="profile-image mb-3">
										<img src="%{user.avatar}" alt="image" class='img-thumbnail' id='profile-image'>
										<div class="update-image">
											<label for=""><i class="bi bi-cloud-arrow-up"></i></label>
											<input type="file" accept='.jpg,.png,.gif,.jpeg' data-uss-image-preview='#profile-image' name='avatar'>
										</div>
									</div>
									<div class="profile-meta">
										<h5 class="text-bold text-dark mb-10 fw-400">%{user.title}</h5>
										<p class="text-sm text-gray">%{user.usercode}</p>
									</div>
								</div>
								
								<?php }, EVENT_ID . "field" );

								/**
								 * Nested Event
								 */
								Events::addListener('udash:pages/profile.left@form', function () {

									/**
									 * Check if user is allowed to update email
									 */
									$lockemail = !!Uss::$global['options']->get('user:lock-email');

									/**
									 * Check if user has an unverified email available
									 */
									$vcode = Uss::$global['usermeta']->get('v-code:update', Uss::$global['user']['id']);

									?>

									<div class="mb-4">
										<label class='form-label --required'>Email</label>
										<input type="email" placeholder="%{user.email}" class='form-control <?php if($lockemail) {
										    echo 'cursor-not-allowed';
										} ?>' value="%{user.email}" <?php echo !$lockemail ? "name='email'" : "disabled"; ?> required>
										
										<?php
                                            if($vcode) {
                                                $email = Uss::$global['usermeta']->get('v-code:email', Uss::$global['user']['id']);
                                                echo "<div class='mt-2 text-center text-muted fs-12px'>
													Please click the link sent to <span class='text-warning'>{$email}</span> to update your email
												</div>";
                                            }
                                            ?>
									</div>
									
								<?php }, EVENT_ID . 'field_100'); 

								# Add Field Button;

								Events::addListener('udash:pages/profile.left@form', function() { ?>

									<input type='hidden' name='nonce' value='%{nonce}'>
									<input type='hidden' name='route' value='profile'>
									
									<button class='btn btn-success w-100 btn-class-1' type='submit'>
										Update
									</button>

								<?php }, EVENT_ID . "field_200" );

									/**
									 * Allow module to add some extra input field!
									 */
									Events::exec('udash:pages/profile.left@form');

    							?>
								
							</fieldset>
						</form>
					</div>
				</div>
				
			</div>
		</div> <!-- end card -->
	</div> <!-- end col -->

<?php
    /**
     * End - udash:pages/profile
     */
}, EVENT_ID . "left");


/**
 * DISPLAY PASSWORD FORM
 */
Events::addListener('udash:pages/profile', function () {
    ?>

<div class="%{profile.col-right}">
	<div class="card settings-card-2 mb-30">
		<div class='card-body'>
		
			<div class='p-3'>
				<div class="lead mb-4">Password</div>
				<form action="" method='POST'>
					<fieldset>
					
						<div class="row">
						
							<div class="col-12">
								<div class="mb-3">
									<input type="password" placeholder="Old Password" class='form-control' name='old_password' required>
								</div>
							</div>
							
							<div class="col-sm-6 mb-3">
								<div class="">
									<label class='form-label'>Password</label>
									<input type="password" placeholder="New Password" class='form-control' name='password' required>
								</div>
							</div>
							
							<div class="col-sm-6 mb-4">
								<div class="">
									<label class='form-label'>Retype Password</label>
									<input type="password" placeholder="Confirm Password" class='form-control' name='confirm_password' required>
								</div>
							</div>
							
							<input type='hidden' name='nonce' value='%{nonce}'>
							<input type='hidden' name='route' value='password'>
							
							<div class="col-12">
								<button class="btn btn-secondary btn-hover w-100 btn-class-2">
									Change Password
								</button>
							</div>
							
						</div>
					</fieldset>
				</form>
			</div>
			
		</div>
	</div> <!-- end card -->
</div> <!-- end col -->

<?php
        /**
         * End - udash:pages/profile
         */
}, EVENT_ID . 'right');
