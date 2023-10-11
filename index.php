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
        'UdTwigExtension.php',
        "UrlGenerator.php",
        'Archive.php',
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
    'Client' => [
        'ClientDashboard.php',
        'Setup.php'
    ],
    'Admin' => [
        'AdminDashboard.php',
        'Setup.php'
    ]
];

$iterateElements($bundles, 'bundles');
$iterateElements($projects, 'src');
