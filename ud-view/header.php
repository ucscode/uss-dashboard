<?php defined('UDASH_DIR') or die; ?>

<main class="main-wrapper %{udash.main.class}">
	
	<?php

    /**
     * Blank Page Test
     */
        if(!self::$config['blank']):

            /**
             * Unlike sidebar,
             * there is no configuration option to remove the header
             *
             * If for any reason you don't want only the header to be available, use the stylesheet
             */

            ?>
	
	<!-- ========== [ HEADER ] ========== -->
	
	<header class="header %{udash.header.class}">
	
		<div class="container-fluid">
			<div class="row align-items-center">
			
				<div class="col-md-6 col-6">
					<div class="header-left d-flex align-items-center">
						<?php
                                    /**
                                     * A sidebar toggle button is irrelevant when the sidebar is disabled
                                     * Thus, will be removed
                                     */
                                     if(!empty(self::$config['sidebar'])):
                                         ?>
						<div class="menu-toggle-btn mr-20 d-xl-none">
							<button id="menu-toggle" class="btn btn-hover">
								<i class="bi bi-list"></i>
							</button>
						</div>
						<?php
                                     endif;

            /**
             * Modules can attach custom stuff to the left side of the header
             */
            Events::exec('udash:header//left');
            ?>
					</div>
				</div>
				
				<div class="col-md-6 col-6">
					<div class="header-right">
					
						<!-- notification start -->
						<?php
                /**
                 * Let create a mini notification box
                 * User will need to click "view all" to see the main notification page
                 *
                 * The location (focus path) of the main notification page can also be changed through the `Udash::config()` method
                 *
                 * Example:
                 * ```php
                 * Udash::config('page:notification', "/path/to/notification");
                 * ```
                 */
                Events::addListener('udash:header//right', function () {
                    require __DIR__ . '/header-notification.php';
                }, EVENT_ID);

            /**
             * Enable right header modification
             */
            Events::exec('udash:header//right');

            ?>
						
						<!-- profile start -->
						<div class="profile-box ml-1 %{udash.userdrop.class}">
						
							<button class="dropdown-toggle bg-transparent border-0" type="button" id="profile" data-bs-toggle="dropdown" aria-expanded="false">
								<div class="profile-info">
									<div class="info">
										<div class="image">
											<img src="<?php echo Udash::user_avatar(Uss::$global['user']['id']); ?>" alt=""/>
											<span class="status"></span>
										</div>
									</div>
								</div>
								<i class="bi bi-chevron-down"></i>
							</button>
							
							<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile">
								<?php
                        if(!empty(Uss::tag('user.title'))):
                            /**
                             * Display user title
                             */
                            ?>
								<li>
									<div class='px-3 py-2 text-end bg-light rounded-2 fs-14px'>
										<span class='text-success'>%{user.title}</span>
										<i class='bi bi-info-circle ms-1'></i>
									</div>
								</li>
								<?php
                        endif;
            /**
             *
             */
            Events::addListener('auth:header//userdrop', function () {
                ?>
									<li data-auth='logout'>
										<a href="<?php echo Core::url(ROOT_DIR . '/' . Udash::config('page:signout')); ?>"> 
											<i class="bi bi-power"></i> Sign Out 
										</a>
									</li>
								<?php }, EVENT_ID);
            /**
             * Execute user dropdown
             * Modules can attach listener to this event
             */
            Events::exec('auth:header//userdrop');
            ?>
							</ul>
							
						</div>
						<!-- profile end -->
						
					</div>
				</div>
				
			</div>
		</div>
		
	</header>
	
	<!-- === [ content-wrapper ] === -->
	
	<div class='content-wrapper %{udash.content.class}'>
		
		<?php
            /**
             * // Blank Page Test
             */
        endif;
