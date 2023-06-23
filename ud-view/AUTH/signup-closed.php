<?php 

defined( 'UDASH_MOD_DIR' ) OR DIE;

/** Column Right */

Uss::tag('col.right', 'd-none');

Events::addListener('@auth//right', null);


/** Column Right */

Uss::tag('col.left', 'col-lg-12');

Events::addListener('@auth//left', function() { ?>

	<div class='vh-100 bg-light d-flex align-items-center justify-content-center text-center auth-bg-image'>
		<div class='vh-100 vw-100 bg-dark bg-opacity-75 position-absolute'></div>
		<div class='position-relative'>
			<h3 class='text-white display-3 p-4'>
				<span class='d-block border-bottom pb-3 mb-2'> Sorry! </span> Signup is not available
			</h3>	
		</div>
	</div>

<?php }, 0);
