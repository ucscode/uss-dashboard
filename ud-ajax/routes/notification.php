<?php

defined('UDASH_AJAX') or die;

Events::addListener('udash:ajax', function () {

    /**
     * Test request nonce
     */
    $trusted = Uss::nonce($_SESSION['uss_session_id'], $_POST['nonce'] ?? null);

    if(!$trusted) {
        Uss::exit(  'The request is not from a trusted source', false);
    }

    /**
     * Loop through $_POST recursively and sanitize all inputs
     */

    array_walk_recursive($_POST, function (&$value, $key) {
        $value = Uss::$global['mysqli']->real_escape_string($value);
    });

    /**
     * - Implode notification ID
     * - Prepare table prefix
     */
    $keys = implode(",", $_POST['nx']);

    $prefix = DB_TABLE_PREFIX;

    /**
     * Handle NX Request based on remark
     */

    switch($_POST['remark']) {

        /**
         * Mark NX as viewed
         */
        case 'viewed':
            $SQL = SQuery::update("{$prefix}_notifications", array( "viewed" => 1 ), "id IN({$keys})");
            break;

            /**
             * Hide Notification
             */
        case 'remove':
            $SQL = SQuery::update("{$prefix}_notifications", array( 'hidden' => 1 ), "id IN({$keys})");
            break;

    };

    # Result Set

    $status = !empty($SQL) ? Uss::$global['mysqli']->query($SQL) : false;
    $message = null;
    $data = [];

    $result = array(
        "status" => &$status,
        "message" => &$message,
        "data" => &$data
    );

    # Execution Phase

    Events::exec("udash:ajax/nx", $result);
    
    # Send Output;
    
    Uss::exit( $result['message'], $result['status'], $result['data'] );

}, 'ajax-nx');
