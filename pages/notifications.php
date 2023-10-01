<?php

defined('ROOT_DIR') || die(':NOTIFICATION');

Uss::instance()->route($pageInfo['route'], function () use ($pageInfo) {

    $udash = Udash::instance();

    $udash->render($pageInfo['template'], [

    ]);

});
