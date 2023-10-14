<?php

$config = (new DashboardConfig())
    ->setBase('/admin')
    ->setTemplateDirectory(AdminDashboard::TEMPLATE_DIR)
    ->setTemplateNamespace('Ua');

AdminDashboard::instance()->configureDashboard($config);