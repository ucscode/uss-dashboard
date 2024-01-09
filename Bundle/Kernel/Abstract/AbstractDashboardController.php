<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Uss\Component\Route\RouteInterface;

class AbstractDashboardController implements RouteInterface
{
    public function onload(array $context): void
    {
        $this->GUIBuilder(
            $context['dashboardInterface'], 
            $context['dashboardDocument'],
            $context['dashboardDocument']?->getCustom('app.form')
        );
    }

    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        // Your code here
    }

    protected function GUIBuilder(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $this->composeApplication($dashboard, $document, $form);
    }
}
