<?php

namespace Module\Dashboard\Foundation\System\Compact;

use Module\Dashboard\Bundle\Document\Interface\DocumentInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Uss\Component\Block\BlockManager;
use Uss\Component\Block\BlockTemplate;
use Uss\Component\Route\RouteInterface;

class DocumentController implements RouteInterface
{
    public function __construct(protected DashboardInterface $dashboard, protected DocumentInterface $document)
    {}

    public function onload(array $routeContext): void
    {
        $controller = $this->document->getController();

        if($controller) {
            $controller->onload($routeContext + [
                'dashboard' => $this->dashboard,
                'document' => $this->document
            ]);
        }

        $this->enableMatchingMenus();

        if(!$this->dashboard->isRendered()) {
            $this->renderTemplateContext();
        }
    }
    
    protected function enableMatchingMenus(): void
    {
        foreach($this->document->getMenuItems() as $node) {
            $autoFocus = $node->getAttribute('auto-focus') ?? true;
            $activated = $node->getAttribute('active');
            if($autoFocus && $activated === null) {
                $node->setAttribute('active', true);
            }
        };
    }

    protected function renderTemplateContext(): void
    {
        $baseLayout = 
            $this->document->getThemeBaseLayout() ?? 
            $this->dashboard->getTheme('base.html.twig');

        $template = $this->document->getTemplate();
        
        if($template !== $baseLayout) {
            $blockTemplate = new BlockTemplate($template, $this->document->getContext());
            $contentBlock = BlockManager::instance()->getBlock('dashboard_content');
            $contentBlock->addTemplate("document_content", $blockTemplate);
        }

        $this->dashboard->render($baseLayout);
    }
}
