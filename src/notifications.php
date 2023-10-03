<?php

defined('ROOT_DIR') || die(':NOTIFICATION');

Uss::instance()->route($pageInfo['route'], function () use ($pageInfo) {

    $ud = Ud::instance();

    $ud->render($pageInfo['template'], [

    ]);

});
