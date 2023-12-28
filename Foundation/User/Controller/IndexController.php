<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Kernel\UssImmutable;
use Uss\Component\Route\RouteInterface;

class IndexController implements RouteInterface
{
    public function onload(array $matches)
    {
        $dashboard = UserDashboard::instance();
        $document = $dashboard->getDocument('index');
        
        $dashboard->render($document->getTemplate(), [
            'official_website' => UssImmutable::PROJECT_WEBSITE,
            'title' => UssImmutable::PROJECT_NAME,
            'dev_email' => UssImmutable::AUTHOR_EMAIL,
            'github_repository' => DashboardImmutable::GITHUB_REPO
        ]);
    }

};
