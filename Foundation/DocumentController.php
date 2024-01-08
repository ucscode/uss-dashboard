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

    public function onload(array $matches)
    {
        $controller = $this->document->getController();

        if($controller) {
            $controller->onload([
                'matches' => $matches,
                'dashboard' => $this->dashboard,
                'document' => $this->document
            ]);
        }
        
        BlockManager::instance()->getBlock('dashboard_content')
            ->addTemplate(
                "content",
                new BlockTemplate($this->document->getTemplate())
            )
        ;

        $context = $this->document->getContext();
        $currentTheme = $this->dashboard->appControl->getThemeFolder();
        $baseTemplate = "@Theme/{$currentTheme}/base.html.twig";

        $this->dashboard->render($baseTemplate, $context);
    }
}
