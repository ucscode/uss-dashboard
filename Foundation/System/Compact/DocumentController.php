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
        $this->enableMatchingMenus();

        $controller = $this->document->getController();
        
        $baseTemplate = sprintf(
            "@Theme/%s/base.html.twig",
            $this->dashboard->appControl->getThemeFolder()
        );
        
        if($controller) {
            $controller->onload($routeContext + [
                'dashboard' => $this->dashboard,
                'document' => $this->document
            ]);
        }

        $template = new BlockTemplate(
            $this->document->getTemplate(),
            $this->document->getContext()
        );

        BlockManager::instance()
            ->getBlock('dashboard_content')
            ->addTemplate("document_content", $template);

        $this->dashboard->render($baseTemplate);
    }

    /**
     * Primary menu are those that auto-active when the route for the document is matched
     */
    protected function enableMatchingMenus(): void
    {
        foreach($this->document->getMenuItems() as $node) {
            $focused = $node->getAttribute('auto-focus') ?? true;
            $activated = $node->getAttribute('active');
            $node->setAttribute('active', $focused && $activated === null);
        };
    }
}
