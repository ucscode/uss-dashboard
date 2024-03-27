<?php

namespace Module\Dashboard\Foundation\System\Compact;

use Module\Dashboard\Bundle\Document\Interface\DocumentInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Uss\Component\Block\BlockManager;
use Uss\Component\Block\BlockTemplate;
use Uss\Component\Route\RouteInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class DocumentController implements RouteInterface
{
    public function __construct(protected DashboardInterface $dashboard, protected DocumentInterface $document)
    {}

    public function onload(ParameterBag $container): Response
    {
        $container->add([
            'dashboard' => $this->dashboard,
            'document' => $this->document
        ]);

        $response = $this->document->getController()?->onload($container);

        return $response ?? $this->getResponse();
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

    protected function getResponse(): Response
    {
        $this->enableMatchingMenus();
        
        $hasThemeIntegration = $this->document->hasThemeIntegration();
        $template = $this->document->getTemplate();
        $context = $this->document->getContext();
        
        $baseLayout = !$hasThemeIntegration ?
            $template : (
                $this->document->getThemeBaseLayout() ?? 
                $this->dashboard->getTheme('base.html.twig')
            );
        
        if($hasThemeIntegration && $template && $template !== $baseLayout) {
            $blockTemplate = new BlockTemplate($template, $context);
            $contentBlock = BlockManager::instance()->getBlock('dashboard_content');
            $contentBlock->addTemplate("document_content", $blockTemplate);
        }

        return $this->dashboard->render($baseLayout, $hasThemeIntegration ? [] : $context);
    }
}
