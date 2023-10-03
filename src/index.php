<?php

defined('Ud::DIR') or die(':INDEX');

/**
 * Create Dashboard Menu
 *
 * It is recommended to create menu outside a focus path
 * This will make it possible for users to access the menu outside the path
*/
/*
Uss::$global['menu']->add('homepage', array(
    'label' => "Dashboard",
    "icon" => "<i class='bi bi-speedometer2'></i>",
    'href' => Uss::instance()->getUrl(ROOT_DIR . "/" . UDASH_ROUTE),
    'active' => implode("/", Uss::instance()->query()) === UDASH_ROUTE,
    'order' => 0
));
*/

// Focus Path;

Uss::instance()->route(Ud::ROUTE, function () use ($pageInfo) {

    /*
    // Authenticate Email Requests

    // require Ud::VIEW_DIR . "/AUTH/@verify-email.php";

    Ud::view(function () {

        Events::instance()->exec('udash:pages/index');

    });
    */

    $this->render($pageInfo['template']);

});
