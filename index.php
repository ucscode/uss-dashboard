<?php

/**
 * Dashboard Module for User Synthetics
 *
 * The user synthetics dashboard module enables developer to build quick backend project with available and powerful control panel.
 * Uss dashboard comes with beautiful and friendly user interface that is easily managable and customizable.
 * It is backed by powerful event driven API to fit the need of any project and also allows modification of the system by other modules.
 *
 * `Ud` means "Uss Dashboard" not "User Dashboard"
 *
 * @version 2.3.0
 * @author ucscode <uche23mail@gmail.com>
 * @link https://github.com/ucscode
 * @copyright Copyright (c) 2023
 * @package Uss\Dashboard
 */

defined('ROOT_DIR') || die("Uss Dashboard: Permission Denied");

if(!defined('UD_DIR')) {
    define('UD_DIR', __DIR__);
};

$resources = [
    'central' => [
        'AbstractUdash.php',
        'Udash.php',
    ],
    'interface' => [
        "UdashFormInterface.php",
        "UserInterface.php",
    ],
    'trait' => [
        //
    ],
    'abstract' => [
        "AbstractUdashForm.php",
    ],
    'class' => [
        "UrlGenerator.php",
        "UdashTwigExtension.php",
        "UdashCrud.php",
        //"DOMTablet.php",
        "Roles.php",
        "Hierarchy.php",
        "User.php",
        "Alert.php",
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
        require_once UD_DIR . "/bundles/{$directory}/{$resourceFile}";
    }
}

# Initialize Udash;

Udash::instance()->init();

/**
 * The uss dashboard module requires database connection to work properly
 * Check if database connect is allowed
 */
