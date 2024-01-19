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

    public function onload(array $routeContext): void
    {
        $this->enableMatchingMenus();

        $controller = $this->document->getController();
        
        if($controller) {
            $controller->onload($routeContext + [
                'dashboardInterface' => $this->dashboard,
                'dashboardDocument' => $this->document
            ]);
        }
        
        $context = $this->document->getContext();
        $template = new BlockTemplate($this->document->getTemplate(), $context);
        $currentTheme = $this->dashboard->appControl->getThemeFolder();
        $baseTemplate = "@Theme/{$currentTheme}/base.html.twig";

        BlockManager::instance()
            ->getBlock('dashboard_content')
            ->addTemplate("native_element", $template);

        $this->dashboard->render($baseTemplate, []);
    }

    /**
     * Primary menu are those that auto-active when the route for the document is matched
     */
    protected function enableMatchingMenus(): void
    {
        $menuItems = $this->document->getMenuItems();
        array_walk($menuItems, function($node) {
            $focused = $node->getAttribute('auto-focus') ?? true;
            $activated = $node->getAttribute('active');
            $node->setAttribute('active', $focused && $activated === null);
        });
    }
}
