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

defined('ROOT_DIR') || die("@USS_DASHBOARD");

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
    'interface' => [
        'UdInterface.php',
        "UdFormInterface.php",
        "UserInterface.php",
    ],
    'trait' => [
        //
    ],
    'abstract' => [
        'AbstractUdBase.php',
        'AbstractUd.php',
        "AbstractUdForm.php",
    ],
    'class' => [
        'Ud.php',
        'Ua.php',
        "UrlGenerator.php",
        "UdCrud.php",
        'Archive.php',
        //"DOMTablet.php",
        "Roles.php",
        "Hierarchy.php",
        "User.php",
        "Alert.php",
        "Paginator.php",
    ],
];

$parseComponents($bundles, 'bundles');

# Initialize Ud;

Ud::instance()->setUp([
    'base' => '/dashboard',
    'namespace' => 'Ud',
    'templatePath' => Ud::TEMPLATE_DIR
]);

Ua::instance()->setUp([
    'base' => '/admin',
    'namespace' => 'Ua',
    'templatePath' => Ua::TEMPLATE_DIR
]);
