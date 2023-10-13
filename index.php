<?php

namespace Ud;

defined('ROOT_DIR') || die("@USS_DASHBOARD");

if(!defined('UD_DIR')) {
    define('UD_DIR', __DIR__);
};

$iterateElements = function (array $element, string $folder): void {
    foreach($element as $directory => $fileList) {
        foreach($fileList as $resourceFile) {
            require_once UD_DIR . "/{$folder}/{$directory}/{$resourceFile}";
        }
    }
};

$bundles = [
    'constants' => [
        'DashboardImmutable.php',
    ],
    'interface' => [
        'DashboardInterface.php',
        "DashboardFormInterface.php",
        "UserInterface.php",
    ],
    'trait' => [
        //
    ],
    'abstract' => [
        'AbstractDashboardComposition.php',
        'AbstractDashboard.php',
        "AbstractDashboardForm.php",
    ],
    'class' => [
        'DashboardConfig.php',
        'DashboardTwigExtension.php',
        "UrlGenerator.php",
        'Archive.php',
        'ArchiveRepository.php',
        "User.php",
        "Alert.php",
        "Paginator.php",
        "UdCrud.php",
        //"DOMTablet.php",
        "Roles.php",
        "Hierarchy.php",
    ],
];

$projects = [
    'User' => [
        'UserDashboard.php',
        'Setup.php'
    ],
    'Admin' => [
        //'AdminDashboard.php',
        //'Setup.php'
    ]
];

$iterateElements($bundles, 'bundles');
$iterateElements($projects, 'src');
