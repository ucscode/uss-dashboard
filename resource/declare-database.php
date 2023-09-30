<?php

# Ensure that this file is required within the Udash instantiation

if(!class_exists('\\Udash') || !isset($this) || !($this instanceof Udash)) {
    die('Udash: This file is not properly implemented');
};

$statements = [];

/**
 * The `users` table
 * This creates the `users` table if it doesn't already exists
 * The table stores only basic information about a user.
 */
$statements[] = "
    CREATE TABLE IF NOT EXISTS %{prefix}users (
        id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL UNIQUE,
        username VARCHAR(25) DEFAULT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        register_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
        usercode VARCHAR(12) NOT NULL UNIQUE,
        last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
        parent INT UNSIGNED DEFAULT NULL,
        FOREIGN KEY(parent) REFERENCES %{prefix}users(id) ON DELETE SET NULL
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
$statements[] = "
    CREATE TABLE IF NOT EXISTS %{prefix}notifications (
        id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
        origin INT,
        model VARCHAR(100) DEFAULT NULL COMMENT 'TYPE: Comment, Reply, Module-Name...',
        userid INT UNSIGNED NOT NULL,
        period TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        message VARCHAR(5000),
        viewed TINYINT NOT NULL DEFAULT 0,
        redirect VARCHAR(255) DEFAULT NULL COMMENT 'URL',
        image VARCHAR(255),
        hidden TINYINT NOT NULL DEFAULT 0,
        FOREIGN KEY(userid) REFERENCES %{prefix}users(id) ON DELETE CASCADE
    )
";

$uss = Uss::instance();

# Execute Statements
foreach($statements as $SQL) {

    try {

        $SQL = $uss->replaceVar($SQL, [
            'prefix' => DB_PREFIX
        ]);

        $result = $uss->mysqli->query($SQL);

        if(!$result) {
            throw new Exception($uss->mysqli->error);
        }

    } catch(Exception $e) {

        $uss->render('@Uss/error.html.twig', [
            "subject" => "Udash: Database Setup Error",
            "message" => $this->getConfig('debug') ? $e->getMessage() : 'MYSQL Error Number: ' . $uss->mysqli->errno
        ]);

        die();

    };

};
