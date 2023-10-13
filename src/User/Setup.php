<?php

$config = (new DashboardConfig())
    ->setBase('/dashboard')
    ->setTemplateNamespace('Ud')
    ->setTemplateDirectory(UserDashboard::TEMPLATE_DIR);

UserDashboard::instance()->configureDashboard($config);
