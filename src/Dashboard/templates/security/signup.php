<?php

defined('UD_DIR') or die;

/** Create signup form */

Events::instance()->addListener('udash:auth.right', function () { ?>

	<form method='post' action="%{udash.ajax}" id='auth-form' data-type='ud-signup' enctype='multipart/form-data'>
		<div class="row py-3">
			<div class="col-sm-10 col-md-9 m-auto">
				
				<?php
                    Events::instance()->addListener('udash:auth/signup@form', function () {
                        /**
                         * Check if username collection is disabled
                         */
                        if(empty(Uss::$global['options']->get("user:collect-username"))) {
                            return;
                        }
                        ?>
					
				<?php }, EVENT_ID . 'field'); ?>
				
				
				<?php
                            Events::instance()->addListener('udash:auth/signup@form', function () {
                                ?>
					
				<?php }, EVENT_ID . 'field_100'); ?>
				
				
				<?php
                                    Events::instance()->addListener('udash:auth/signup@form', function () {
                                        ?>
					<!-- end col -->
					
				<?php }, EVENT_ID . 'field_200'); ?>
				
				
				<?php
                                            Events::instance()->addListener('udash:auth/signup@form', function () {
                                                ?>
					<!-- end col -->
					
				<?php }, EVENT_ID . 'field_300'); ?>
				
				
				<?php
                                                    Events::instance()->addListener('udash:auth/signup@form', function () {
                                                        ?>
					<!-- end col -->
					
				<?php }, EVENT_ID . 'field_400'); ?>
				
				
				<?php
                                                            Events::instance()->addListener('udash:auth/signup@form', function () {
                                                                if(empty(Uss::$global['options']->get('user:affiliation'))) {
                                                                    return;
                                                                }
                                                                $parent = Ud::get_sponsor();
                                                                if(!$parent) {
                                                                    return;
                                                                }
                                                                ?>
				<?php }, EVENT_ID . 'field_500'); ?>
				
				
				<?php
                                                                Events::instance()->addListener('udash:auth/signup@form', function () {
                                                                    ?>
					<!-- end col -->
					
				<?php }, EVENT_ID . 'field_600'); ?>
				
				
				<?php Events::instance()->exec('udash:auth/signup@form'); ?>
				
			</div>
		</div>
	<!-- end row -->
	</form>
	
<?php }, EVENT_ID . 'signup'); ?>


<?php
    Events::instance()->addListener('udash:auth.right', function () { ?>
		
	<?php }, EVENT_ID . 'signup_100');
?>


