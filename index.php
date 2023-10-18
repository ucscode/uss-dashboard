<?php

namespace Ud;

use DashboardImmutable;

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
        'RoleImmutable.php',
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
        "AbstractUserFoundation.php",
        "AbstractUserRepository.php",
    ],
    'class' => [
        'DashboardTwigExtension.php',
        'Archive.php',
        'ArchiveRepository.php',
        "User.php",
        "Alert.php",
        "Paginator.php",
        "FileUploader.php",
        "UdCrud.php",
        //"DOMTablet.php",
        "Hierarchy.php",
    ],
];

$projects = [
    'User' => [
        'UserDashboard.php',
    ],
    'Admin' => [
        'AdminDashboard.php',
    ],
];

$iterateElements($bundles, 'bundles');
$iterateElements($projects, 'src');

require_once DashboardImmutable::SRC_DIR . "/ConfigurePanel.php";