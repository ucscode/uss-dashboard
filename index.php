<?php

/**
 * Dashboard Module for User Synthetics
 *
 * The user synthetics dashboard module enables developer to build quick user-based project without need of re-creating a user management system.
 * uss dashboard comes with beautiful and friendly user interface that is easily managable and customizable.
 * It is backed by powerful event driven API to fit the need of any project and also allows modification of the system by other modules.
 *
 * `ud` &mdash; represents `uss` `dashboard`
 *
 * @version 2.3.0
 * @author ucscode <uche23mail@gmail.com>
 * @link https://github.com/ucscode
 * @copyright Copyright (c) 2023
 * @package Uss\Dashboard
 */

defined('ROOT_DIR') || die("Udash cannot be accessible directly");

/**
 * Get Base Uss Dashboard Class
 * The class is "final" and cannot be extended
 */
require_once __DIR__ . "/AbstractUdash.php";
require_once __DIR__ . "/Udash.php";

/**
 * Load Udash Resources:
 * These includes Classes, Enums, Traits, Abstract
 */
$resources = [
    'interface' => [
        "UdashFormInterface.php",
    ],
    'trait' => [
        //"UdashFormTrait.php",
    ],
    'abstract' => [
        "AbstractUdashForm.php",
    ],
    'class' => [
        "UdashTwigExtension.php",
        "UdashCrud.php",
        //"DOMTablet.php",
        "Roles.php",
        "Hierarchy.php"
    ],
    'forms' => [
        "UdashLoginForm.php",
        "UdashRegisterForm.php",
        "UdashRecoveryForm.php"
    ]
];

foreach($resources as $directory => $fileList) {
    # Iterate Folder
    foreach($fileList as $resourceFile) {
        # Include Files
        require_once Udash::RES_DIR . "/{$directory}/{$resourceFile}";
    }
}

# Initialize Udash;

Udash::instance()->init();

/**
 * The uss dashboard module requires database connection to work properly
 * Check if database connect is allowed
 */
