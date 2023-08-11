<?php

/**
 * Create and initialize dashboard database
 * This file will check for existence of any default database table and create it if it doesn't exist!
 */
defined("UDASH_DIR") or die;

/**
 * Let's handle the database table management process using an anomymous class
 */
new class () {
    /**
     * Database Table Prefix
     * Defined in the `uss-conn.php` file in the installation directory
     *
     * @var string
     * @ignore
     */
    public $prefix = DB_TABLE_PREFIX;

    /**
     * An array run initial database query
     *
     * @var array
     * @ignore
     */
    protected $initQuery = array();

    /**
     * Database Connection Manager Constructor
     */
    public function __construct()
    {
        $this->loadInitQuery($this->prefix);
        $this->runInitQuery();
        $this->initMetaTable($this->prefix);
    }

    /**
     * Load the initial SQL Query that will execute during runtime
     */
    protected function loadInitQuery(string $prefix)
    {

        /**
         * The `users` table
         * This creates the `users` table if it doesn't already exists
         * The table stores only basic information about a user.
         */
        $this->initQuery[] = "
			CREATE TABLE IF NOT EXISTS {$prefix}_users (
				id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
				email VARCHAR(255) NOT NULL UNIQUE,
				username VARCHAR(25) DEFAULT NULL UNIQUE,
				password VARCHAR(255) NOT NULL,
				register_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				usercode VARCHAR(12) NOT NULL UNIQUE,
				last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
				parent INT DEFAULT NULL,
				FOREIGN KEY(parent) REFERENCES {$prefix}_users(id) ON DELETE SET NULL
			)
		";

        /**
         * The `notification` table
         * This table is responsible for management of push notifications messages
         *
         * - origin: The source (user) which triggered the notification
         * - model: The type of notification that was send. It may also be used to identify which module processed the notification.
         * - userid: The user receiving the notification
         * - period: The time at which the notification was created
         * - message: The message sent to the user
         * - viewed: Indicates whether the user has viewed the notification or not
         * - redirect: A URL to visit when the user clicks on the notification
         * - image: The thumbnail that will be displayed by the left side of the notification
         * - hidden: Make visible or invisible to the user
         */
        $this->initQuery[] = "
			CREATE TABLE IF NOT EXISTS {$prefix}_notifications (
				id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
				origin INT,
				model VARCHAR(100) DEFAULT NULL COMMENT 'TYPE: Comment, Reply, Module-Name...',
				userid INT NOT NULL,
				period TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				message VARCHAR(5000),
				viewed TINYINT NOT NULL DEFAULT 0,
				redirect VARCHAR(255) DEFAULT NULL COMMENT 'URL',
				image VARCHAR(255),
				hidden TINYINT NOT NULL DEFAULT 0,
				FOREIGN KEY(userid) REFERENCES {$prefix}_users(id) ON DELETE CASCADE
			)
		";

    }

    /**
     * Run all the loaded queries
     */
    protected function runInitQuery()
    {

        foreach($this->initQuery as $SQL) {
            $result = Uss::$global['mysqli']->query($SQL);
        };

    }

    /**
     * The usermeta table
     *
     * The usermeta table is automatically created and managed by the `pairs` class.
     * The table stores addition information about a user.
     * Thus, it is more efficient and optimizable to store extra user data into this table than creating new
     * columns under the main user table
     *
     * @see \pairs
     */
    protected function initMetaTable(string $prefix)
    {

        /**
         * Instantiate the pairs class
         */
        Uss::$global['usermeta'] = new Pairs(Uss::$global['mysqli'], "{$prefix}_usermeta");

        /**
         * The user meta table will be linked to the main users table.
         * It will get bounded by a FOREIGN KEY Constraint
         */
        Uss::$global['usermeta']->linkParentTable("{$prefix}_users", "{$prefix}_users");

    }

};
