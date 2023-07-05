<?php

defined('UDASH_DIR') or die;

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
        'href' => Core::url(ROOT_DIR . "/{$hierFocus}"),
        "active" => implode("/", Uss::query()) === $hierFocus
    ));

    /**
     * Create a focus
     * The URI required to view the content
     * `(?:/?\w+)?` expression accepts extra query string containing usercode
     * This will enable you view hierarchy of a particular downlines
     */
    Uss::route($hierFocus . "(?:/?\w+)?", function () use ($hierFocus) {

        Events::addListener('@head:after', function () {
            /**
             * Get CSS that will style the nodes
             */
            $dir = Core::url(Udash::ASSETS_DIR . "/vendor/datatree");
            echo "\t<link rel='stylesheet' href='{$dir}/treeNode.css'/>\n";

        }, EVENT_ID . "node");

        Events::addListener('@body:after', function () use ($hierFocus) {

            /**
             * Get JavaScript the will render the nodes
             */
            $dir = Core::url(Udash::ASSETS_DIR . "/vendor/datatree");
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
        Udash::view(function () {
            /**
             * Create Event
             */
            Events::addListener('udash:pages/hierarchy', function () {
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
            Events::exec('udash:pages/hierarchy');

        }); // End view

    });

});
