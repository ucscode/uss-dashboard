<?php

defined('UD_DIR') or die(':HIERARCHY');

call_user_func(function () use ($profileFocus) {

    /**
     * Check if affiliation is turned off
     * If it is, ignore this functionality
     */
    if(empty(Uss::$global['options']->get('user:affiliation'))) {
        return;
    }

    // The Focus Expression
    $hierFocus = $profileFocus . "/hierarchy";

    /**
     * Create a menu to access the page
     */
    Uss::$global['menu']->get('profile')->add('team-tree', array(
        'label' => "Hierarchy",
        'href' => Uss::instance()->getUrl(ROOT_DIR . "/{$hierFocus}"),
        "active" => implode("/", Uss::instance()->query()) === $hierFocus
    ));

    /**
     * Create a focus
     * The URI required to view the content
     * `(?:/?\w+)?` expression accepts extra query string containing usercode
     * This will enable you view hierarchy of a particular downlines
     */
    Uss::instance()->route($hierFocus . "(?:/?\w+)?", function () use ($hierFocus) {

        Events::instance()->addListener('@head:after', function () {
            /**
             * Get CSS that will style the nodes
             */
            $dir = Uss::instance()->getUrl(Ud::ASSETS_DIR . "/vendor/datatree");
            echo "\t<link rel='stylesheet' href='{$dir}/treeNode.css'/>\n";

        }, EVENT_ID . "node");

        Events::instance()->addListener('@body:after', function () use ($hierFocus) {

            /**
             * Get JavaScript the will render the nodes
             */
            $dir = Uss::instance()->getUrl(Ud::ASSETS_DIR . "/vendor/datatree");
            echo "\t<script src='{$dir}/treeData.js'></script>\n";

            /**
             * Require the PHP file that will build the node
             * And then render it
             */
            require_once __DIR__ . '/SECTIONS/treedata.php';

        }, EVENT_ID . "node");

        /**
         * Display the hierarchy tree
         */
        Ud::view(function () {
            /**
             * Create Event
             */
            Events::instance()->addListener('udash:pages/hierarchy', function () {
                ?>
			<div class='container-fluid'>
				<div class='tree-container position-relative rounded-2'>
					<div id='hierarchy' class='overflow-auto'></div>
				</div>
			</div>
		<?php }, EVENT_ID . 'hierarchy'); // End Event

            /**
             * Execute Events
             */
            Events::instance()->exec('udash:pages/hierarchy');

        }); // End view

    });

});
