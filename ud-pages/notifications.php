<?php

defined('UDASH_DIR') or die;

Uss::route(Udash::config('page:notification'), function () {


    /**
     * Process the SQL Queries required to display notification
     */
    $notify = (Uss::$global['user']) ? (require __DIR__ . '/SECTIONS/notification-query.php') : null;


    /**
     * Render column size for the column element
     * This will enable modules alter the template or design of the notification page
     */
    Uss::tag('udash.nx.cols', 'col-md-10 col-lg-9 m-auto', false);


    /**
     * Now let's render the output
     * By using the variable which contains information about the notification
     */
    Udash::view(function () use ($notify) { ?>
		
		<div class='container-fluid'>
			<div class='row'>
				
				<?php
                    /**
                     * THE MARKER EVENT
                     */
                    Events::addListener('udash:nx', function ($notify) {
                        /**
                         * Add a button to mark all visible notifications as read
                         */
                        if(!$notify['query']->num_rows) {
                            return;
                        }
                        ?>
					<div class='text-end mb-2 nx-marker %{udash.nx.cols}'>
						<a href='javascript:void(0)' class='btn btn-sm btn-outline-success' data-nx-marker>
							Mark all as read
						</a>
					</div>
				<?php }, EVENT_ID . 'nx'); ?>
				
				
				<?php

                            /**
                             * THE NOTIFICATION BLOCK EVENT
                             */

                            Events::addListener('udash:nx', function ($notify) {

                                /**
                                 * First, let create add a "delete" action to each notification
                                 *
                                 * More custom actions can be added by adding listener to the `udash:nx//action` events
                                 * Hence, any module can attach a listener to expand the action
                                 *
                                 * The current notification being looped is passed as the argument
                                 *
                                 */

                                Events::addListener('udash:nx.action', function ($notification) {
                                    ?>
							<li class="dropdown-item">
								<a href="javascript:void(0)" class="text-gray text-sm d-block" data-nx-action='remove'>
									<i class='bi bi-trash me-1'></i> Remove
								</a>
							</li>
						<?php }, EVENT_ID . 'action'); // Notification Action

                                /**
                                 * Output the notification
                                 */

                                ?>
				
						<div class='mb-4 nx-block %{udash.nx.cols}' data-nxurl='%{udash.ajax}'>
							<div class='card' id='notification-list' data-nx-container='body'>
																	
								<?php

                                                /**  If notification exists  */

                                                if($notify['query']->num_rows):


                                                    /** Loop available notifications */

                                                    while($notification = $notify['query']->fetch_assoc()):

                                                        // Get the image

                                                        $image = $notification['image'];

                                                        if(empty($image)) {
                                                            $image = Udash::user_avatar($notification['origin']);
                                                        }

                                                        /**
                                                         * uss dashboard notification uses markdown language
                                                         * Convert markdown into HTML entities
                                                         * Do not use HTML Tags like <br> as it will be converted to `&lt;br&gt;`
                                                         */

                                                        $markdown = call_user_func(function () use ($notification) {
                                                            $message = htmlspecialchars(trim($notification['message']));
                                                            return (new Parsedown())->text($message);
                                                        });

                                                        /**
                                                         * Get the redirect URL
                                                         * This is the URL the end-user will be taken to when they click the notification
                                                         *
                                                         * TIP FOR BETTER REDIRECT
                                                         * ------------------------
                                                         * If a redirect request needs to be made dynamic,
                                                         * the best option will be to redirect to a controllable URL and then handle the request manually.
                                                         *
                                                         * EXAMPLE:
                                                         *
                                                         * Redirect URL = https://domain.com/focus/path/{userid}
                                                         *
                                                         * ```php
                                                         * Uss::route( "focus/path/(\d+)", function($match) {
                                                         *
                                                         * 	$isAdmin = Roles::user($match[1])::is( 'administrator' );
                                                         *
                                                         * 	// alternatively, you can use `Uss::query(2)` instead of `$match[1]`
                                                         *
                                                         * 	if( $isAdmin ) {
                                                         *
                                                         * 		"redirect to custom admin page";
                                                         * 		"Or display custom content";
                                                         *
                                                         * 	} else "redirect to client page";
                                                         *
                                                         * });
                                                         * ```
                                                         */
                                                        $redirect = $notification['redirect'] ?? 'javascript:void(0)';

                                                        $viewed = empty($notification['viewed']) ? 'unviewed' : null;

                                                        ?>
								
								<div class="single-notification p-3">
									
									<a href='<?php echo $redirect; ?>' class='notification-redirect <?php echo $viewed; ?>' data-nx='<?php echo $notification['id']; ?>'></a>
									
									<div class="notification">
										<div class="image ">
											<img src='<?php echo $image; ?>' class=''>
										</div>
										<div class="content">
											<div class='notification-wrapper text-sm text-gray'>
												<?php echo $markdown; ?>
											</div>
											<span class="text-sm text-medium text-gray">
												<?php echo Core::elapse($notification['period']); ?> 
												<?php if(!empty($notification['redirect'])): ?>
													<i class='mdi mdi-vector-link ms-1'></i>
												<?php endif; ?>
											</span>
										</div>
									</div>

									<div class="action %{udash.nx.action.class}">
									
										<button class="more-btn dropdown-toggle" id="moreAction" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="bi bi-three-dots"></i>
										</button>
										
										<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="moreAction">
											<?php
                                                                        /**
                                                                         * Allow module to add custom action;
                                                                         *
                                                                         * Do execute an event inside a loop and at the same time,
                                                                         * Add listener to the event within the same loop
                                                                         */
                                                                        Events::exec('udash:nx.action', $notification);
                                                        ?>
										</ul>
										
									</div>
								  
								</div>
								
								<?php
                                                    endwhile;

                                                else:

                                                    ?>
								
									<div class='text-center py-5'>
										<div class='px-md-5'>
											
											<div class='row'>
												<div class='col-sm-8 m-auto col-lg-6'>
													<img src='<?php echo Core::url(Udash::ASSETS_DIR . '/images/notification-empty.webp'); ?>' class='img-fluid'>
												</div>
											</div>
											
											<h2 class='text-capitalize fw-light'>No Notifications <br> to display</h2>
										
										</div>
									</div>
									
								<?php endif; ?>
									
							</div>
						</div>
				<?php }, EVENT_ID . 'nx_100'); ?>
				
					
				<?php
                    Events::addListener('udash:nx', function ($notify) {

                        /**
                         * If there's neither a next nor previous page
                         * Then, pagination is not required
                         */
                        if(empty($notify['next']) && empty($notify['prev'])) {
                            return;
                        }

                        // We need a single column button
                        $cols = 1;

                        /**
                         * However, if both next and previous page exists
                         * We need a double column row to display both buttons
                         */
                        if($notify['prev'] && $notify['next']) {
                            $cols++;
                        }

                        $cols = (12 / $cols);

                        /**
                         * Now let's build an HTTP Query
                         * However, we must unset the query to avoid duplicating it
                         * since the "query" in the URL is disguised as a normal path
                         */
                        $query = $_GET;
                        unset($query['query']);

                        ?>
					<div class='%{udash.nx.cols}'>
						<div class='row'>
						
							<?php
                                        /**
                                         * If there is a next page,
                                         * Display a next button
                                         *
                                         * Next shows "previous" notification because the query was ordered in descending order
                                         */
                                        if($notify['next']): ?>
								<div class='col-sm-<?php echo $col; ?>'>
									<a href='?<?php echo http_build_query(array_merge($query, array('view' => $notify['next']))); ?>' class='btn btn-secondary w-100 text-sm'>
										<i class='bi bi-chevron-bar-left'></i> Older
									</a>
								</div>
							<?php endif; ?>
							
							<?php
                                        /**
                                         * If there is a previous page,
                                         * Display a previous button
                                         *
                                         * Prev shows "newer" notification because the query was ordered in descending order
                                         */
                                        if($notify['prev']): ?>
								<div class='col-sm-<?php echo $col; ?>'>
									<a href='?<?php echo http_build_query(array_merge($query, array('view' => $notify['prev']))); ?>' class='btn btn-success w-100 text-sm'>
										Newer <i class='bi bi-chevron-bar-right'></i> 
									</a>
								</div>
							<?php endif; ?>
							
						</div>
					</div>
				<?php }, EVENT_ID . 'nx_200'); ?>
				
				<?php

                    /**
                     * THE EVENT DISPATCHER
                     *
                     * Add, modify remove events on this page
                     */
                    Events::exec('udash:nx', $notify);
        ?>
				
			</div>
		</div>
		
	<?php });

});
