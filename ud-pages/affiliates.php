<?php


defined('UDASH_DIR') or die;

call_user_func(function () use ($profileFocus) {

    /**
     * Ignore this section if affiliation is turned off
     */
    if(empty(Uss::$global['options']->get('user:affiliation'))) {
        return;
    }

    /**
     * Create The Focus URI
     */
    $teamFocus = $profileFocus . "/team";

    /**
     * Create the affiliation menu
     * A child menu under profile
     */
    Uss::$global['menu']->get('profile')->add('team', array(
        "label" => "My Team",
        "href" => Core::url(ROOT_DIR . "/{$teamFocus}"),
        "active" => implode("/", Uss::query()) === $teamFocus
    ));

    /**
     * DISPLAY TEAM LIST
     */
    Uss::route($teamFocus, function () {

        Udash::view(function () {

            $userid = Uss::$global['user']['id'];

            $hierarchy = new hierarchy();

            /**
             * Find all the descendants/referrals of the current user
             * returns a MYSQLI result
             * @see hierarchy
             */
            $result = $hierarchy->descendants_of($userid);

            /**
             * Generate a table
             * And push the MYSQL result into it
             */
            $table = new DOMTablet('affiliates');
            $table->data($result);

            /**
             * Determine which columns should display
             * And what title the column should have
             */
            $columns = array(
                'mail' => 'email',
                'usercode' => 'referral code',
                'register_time' => 'registered',
                'depth' => 'level'
            );

            /**
             * Check if collection of username is allowed
             * If it is, add username to the display list
             */
            if(Uss::$global['options']->get('user:collect-username')) {
                array_unshift($columns, 'username');
            }

            /**
             * Push the columns information into the table
             */
            $table->columns($columns);

            /**
             * Automatically wrap the table around a card
             */
            $table->wrap('container-fluid');

            /**
             * Add Event Listener
             */
            Events::addListener('udash:pages/affiliate', function ($data) {

                /**
                 * Prepare and display the table
                 */
                $data['table']->prepare(function ($data) {
                    // make email clickable
                    $data['mail'] = "<a href='mailto:{$data['email']}'>{$data['email']}</a>";
                    return $data;
                }, true);

            }, EVENT_ID . 'table');

            /**
             * Execute Event
             */
            Events::exec('udash:pages/affiliate', array( 'table' => &$table ));

        });

    });

});
