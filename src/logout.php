<?php

defined('ROOT_DIR') || die(':LOGOUT');

Uss::instance()->route($pageInfo['route'], function () use ($pageInfo) {

    if(isset($_SESSION['UssUser'])) {
        unset($_SESSION['UssUser']);
    };

    $endpoint = $pageInfo['endpoint'] ?? null;

    if(!($endpoint instanceof UrlGenerator) && !is_string($endpoint)) {
        $endpoint = new UrlGenerator();
    };

    header("location: " . $endpoint);
    exit;

});
