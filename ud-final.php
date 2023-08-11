<?php

defined('UDASH_DIR') or die;

/**
 * Further Configuration && Universal Pages
 *
 * Pages like "notification page" or "logout page" are considered universal
 * This is because that they don't rely on a single focus URI
 *
 * For example:
 *
 * - a forum,
 * - an admin panel,
 * - a membership subscription,
 * - a social media channel,
 *
 * and many other platform requires a "logout page" and even a "login page".
 *
 * Such pages in uss dashboard are known as `Universal Pages` because they can be used across multiple channel
 * Therefore, the focus expression for universal pages are handled by `Udash::config`
 *
 */

/**
 * If the focus expression for any page is not undefined, the default will be used

 * @var array
 */

$defaultPagesExpr = array(

    // The page to output notifications
    'page:notification' => UDASH_ROUTE . '/notifications',

    // The focal expression to sign out
    'page:signout' => UDASH_ROUTE . '/signout',

    // The redirection url after signing out
    'signout:redirect' => Core::url(ROOT_DIR . '/' . UDASH_ROUTE)

);


// ------------ [{ Add pages to configuration }] -----------

foreach($defaultPagesExpr as $key => $value) {

    if(!Udash::config($key)) {
        
        Udash::config($key, $value);

    };

};


// ------------- [{ Get all Global Pages }] ----------------

require Udash::PAGES_DIR . '/notifications.php';
require Udash::PAGES_DIR . '/signout.php';
