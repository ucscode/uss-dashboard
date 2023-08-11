<?php


defined('UDASH_DIR') or die;

/**
 * Create Dashboard Menu
 *
 * It is recommended to create menu outside a focus path
 * This will make it possible for users to access the menu outside the path
*/
Uss::$global['menu']->add('homepage', array(
    'label' => "Dashboard",
    "icon" => "<i class='bi bi-speedometer2'></i>",
    'href' => Core::url(ROOT_DIR . "/" . UDASH_ROUTE),
    'active' => implode("/", Uss::query()) === UDASH_ROUTE,
    'order' => 0
));


// Focus Path;

Uss::route(UDASH_ROUTE, function () {

    // Authenticate Email Requests

    require Udash::VIEW_DIR . "/AUTH/@verify-email.php";

    Udash::view(function () {

        /**
         * The index page is empty
         * A module needs to fill it up by adding an event listener;
        */

        Events::exec('udash:pages/index');

    });

});
