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

$parseComponents = function (array $components, string $folder): void {
    foreach($components as $directory => $fileList) {
        foreach($fileList as $resourceFile) {
            require_once UD_DIR . "/{$folder}/{$directory}/{$resourceFile}";
        }
    }
};

$bundles = [
    'central' => [
        'AbstractUd.php',
        'Ud.php',
        'UdPage.php',
    ],
    'interface' => [
        "UdFormInterface.php",
        "UserInterface.php",
    ],
    'trait' => [
        //
    ],
    'abstract' => [
        "AbstractUdForm.php",
    ],
    'class' => [
        "UrlGenerator.php",
        "UdTwigExtension.php",
        "UdCrud.php",
        //"DOMTablet.php",
        "Roles.php",
        "Hierarchy.php",
        "User.php",
        "Alert.php",
    ],
];

$source = [
    'forms' => [
        "UdLoginForm.php",
        "UdRegisterForm.php",
        "UdRecoveryForm.php",
    ],
    'controllers' => [
        //'LoginController.php',
        'RegisterController.php',
        'RecoveryController.php',
        'IndexController.php',
        'LogoutController.php',
        'NotificationController.php',
    ]
];

$parseComponents($bundles, 'bundles');
$parseComponents($source, 'src');

# Initialize Ud;

Ud::instance()->init();
