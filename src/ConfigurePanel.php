<?php

UserDashboard::instance()->configureDashboard("/dashboard");
AdminDashboard::instance()->configureDashboard("/admin");

Event::instance()->addListener('modules:loaded', function () {
    Event::instance()->emit('dashboard:render');
}, -9);