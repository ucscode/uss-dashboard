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
        $this->enablePrimaryMenus();

        $controller = $this->document->getController();
        
        if($controller) {
            $controller->onload($context + [
                'dashboardInterface' => $this->dashboard,
                'dashboardDocument' => $this->document
            ]);
        }
        
        $template = new BlockTemplate($this->document->getTemplate());
        $context = $this->document->getContext();
        $currentTheme = $this->dashboard->appControl->getThemeFolder();
        $baseTemplate = "@Theme/{$currentTheme}/base.html.twig";

        BlockManager::instance()
            ->getBlock('dashboard_content')
            ->addTemplate("native_element", $template);

        $this->dashboard->render($baseTemplate, $context);
    }

    /**
     * Primary menu are those that auto-active when the route for the document is matched
     */
    protected function enablePrimaryMenus(): void
    {
        foreach($this->document->getMenuItems() as $node) {
            $autoFocus = $node->getAttribute('autoFocus') ?? true;
            $activeState = $node->getAttribute('active');
            if($autoFocus && $activeState === null) {
                $node->setAttribute('active', true);
            }
        }
    }
}
