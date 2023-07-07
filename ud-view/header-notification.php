<?php

defined('UDASH_DIR') or die;

$notice = call_user_func(function () {

    /**
     * Details about user notification will be stored inside the array below
     *
     * @var array $data
     */
    $data = array();

    // Let declare out database prefix since constant can't be used inside a string
    $prefix = DB_TABLE_PREFIX;

    /**
     * If a user can see the dashboard,
     * It means the user is logged in.
     * So let's get the user id
     */
    $userid = Uss::$global['user']['id'];


    /**
     * The maximum number of notification to display on the small box
     * More notification will be visible when "view all" is clicked
     */
    $limit = 3;


    /**
     * Get the number of notifications that the user received but has not clicked
     *
     * The number will be displayed above the notification button to indicate to that
     * the user has "x" number of unread notifications available
     */

    $views = Uss::$global['mysqli']->query("
			SELECT COUNT(viewed) AS unviewed
			FROM {$prefix}_notifications
			WHERE userid = {$userid}
				AND viewed = 0
				AND hidden = 0
			GROUP BY userid
		")->fetch_assoc();

    /** Store the detail */
    $data['alert'] = $views ? $views['unviewed'] : 0;


    /**
     * Great!
     * Now our next step is to get the most recent notification received
     */

    $data['notify'] = Uss::$global['mysqli']->query("
			SELECT * FROM {$prefix}_notifications
			WHERE userid = {$userid}
				AND hidden = 0
			ORDER BY id DESC
			LIMIT {$limit} 
		");

    return $data;

});


/**
 * The notification url is the page where a full list notifications will be displayed
 */

$notify_url = Core::url(ROOT_DIR) . "/" . Udash::config('page:notification');

?>
	<div class="notification-box ml-15" data-nxurl='%{udash.ajax}'>
			
		<?php

            /**
             * The dropdown notification box is only available to larger screens
             * that is different from... `Balablu`
             *
             */

            $widescreen_attrs = array(
                'class' => "dropdown-toggle",
                'id' => "notification",
                'data-bs-toggle' => "dropdown",
                'aria-expanded' => "false",
                'type' => "button"
            );

$mobile_attrs = array(
    'class' => 'mobile-notice',
    'type' => "button"
);


/**
 * Create Notification button
 */

$button = function (array $attrs) use ($notice) {

    /** convert array into HTML attribute */
    ?>
			<button <?php echo Core::array_to_html_attrs($attrs); ?>>
				<i class="bi bi-bell"></i>
				<?php
                /**
                 * If there is an unread notification
                 * Display an animated number to capture the user's attention
                 */
                if(!empty($notice['alert'])):
                    ?>
					<span class='animate__animated animate__wobble animate__infinite' data-nx-count>
						<?php echo $notice['alert']; ?>
					</span>
				<?php endif; ?>
			</button>
		<?php
                    /** End the button function */
};

/**
 * Now let create button for small and large screen
 *
 * On very small screen, user will be taken straigh to the notification page
 * On large screen, user will view the dropdown and will have option to click "view all"
 */
?>
			
			<div class='mobile d-sm-none'>
				<a href='<?php echo $notify_url; ?>'>
					<?php echo $button($mobile_attrs); ?>
				</a>
			</div>
			
			<div class='widescreen d-none d-sm-flex'>
			
				<?php echo $button($widescreen_attrs); ?>
		
				<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notification" data-nx-container='header'>
					<?php

                while($notification = $notice['notify']->fetch_assoc()):

                    /**
                     * Get an image to display beside the notification
                     */
                    $image = $notification['image'];

                    if(empty($image)) {
                        $image = Udash::user_avatar($notification['origin']);
                        if(empty($image)) {
                            $image = Udash::user_avatar($notification['userid']);
                        }
                    };

                    /**
                     * uss notification uses pure markdown
                     * Not HTML tag is allowed.
                     * Tags like <br> will be encoded to `&lt;br&gt;`
                     */
                    $markdown = call_user_func(function () use ($notification) {
                        $text = htmlspecialchars(trim($notification['message']));
                        return (new Parsedown())->text($text);
                    });

                    /**
                     * Max length of text that should be rendered
                     * before showing the ellipse (`...`) symbol
                     */
                    $length = 124;
                    $message = trim(strip_tags($markdown));
                    $message = substr($message, 0, $length);

                    if(strlen($notification['message']) > $length) {
                        $message .= "&hellip;";
                    }

                    /**
                     * When user clicks on the notification block
                     * They may get redirected if a URL is passed along with the notification
                     */

                    if(empty($notification['redirect'])) {
                        $notification['redirect'] = 'javascript:void(0)';
                    }

                    /**
                     * Color the block
                     * If user has not viewed a particular notification, it will be highlighted
                     */
                    $class = empty($notification['viewed']) ? 'unviewed' : null;

                    ?>
					<li>
						<a href="<?php echo $notification['redirect']; ?>" class='<?php echo $class; ?>' data-nx='<?php echo $notification['id']; ?>'>
							<div class="image">
								<img src="<?php echo $image; ?>" alt="" />
							</div>
							<div class="content">
								<p><?php echo $message; ?></p>
								<span><?php echo Core::elapse($notification['period']); ?></span>
							</div>
						</a>
					</li>
					<?php endwhile; ?>
					
					<?php
                        /**
                         * After displaying a limited list of notifications
                         * Provide a "view all" button at the end
                         */
?>
					<li>
						<a href="<?php echo $notify_url; ?>" class='d-block text-center'>
							<small> <i class='bi bi-bell me-1'></i> View all </small>
						</a>
					</li>
				</ul>
				
			</div>
		
	</div>
	