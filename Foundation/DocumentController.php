<?php

namespace Module\Dashboard\Foundation;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Uss\Component\Block\BlockManager;
use Uss\Component\Block\BlockTemplate;
use Uss\Component\Route\RouteInterface;

class DocumentController implements RouteInterface
{
    public function __construct(protected DashboardInterface $dashboard, protected Document $document)
    {
    }

    public function onload(array $context): void
    {
        $controller = $this->document->getController();
        
        if($controller) {
            $controller->onload($context + [
                'dashboardInterface' => $this->dashboard,
                'dashboardDocument' => $this->document
            ]);
        }
        
        BlockManager::instance()->getBlock('dashboard_content')
            ->addTemplate(
                "content_content",
                new BlockTemplate($this->document->getTemplate())
            )
        ;

        $context = $this->document->getContext();
        $currentTheme = $this->dashboard->appControl->getThemeFolder();
        $baseTemplate = "@Theme/{$currentTheme}/base.html.twig";

        $this->dashboard->render($baseTemplate, $context);
    }
}
