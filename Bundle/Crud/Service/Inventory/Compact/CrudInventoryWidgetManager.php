<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudWidgetInterface;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Uss\Component\Block\BlockTemplate;
use Uss\Component\Kernel\Uss;

class CrudInventoryWidgetManager
{
    protected Uss $uss;

    public function __construct(protected CrudInventoryInterface $crudInventory)
    {
        $this->uss = Uss::instance();
        $widgetBlocks = $this->getWidgetBlocks();
        $widgetHTMLContext = $this->getWidgetHTMLContext($widgetBlocks);
        $this->exportWidgets($widgetHTMLContext);
    }

    protected function getWidgetBlocks(): array
    {
        $widgetBlocks = array_map(function(CrudWidgetInterface $widgetInterface) {
            return $widgetInterface->createWidget($this->crudInventory);
        }, $this->crudInventory->getWidgets());

        usort($widgetBlocks, fn ($a, $b) => $a->priority() <=> $b->priority());

        return $widgetBlocks;
    }

    protected function getWidgetHTMLContext(array $widgetBlocks): array
    {
        $filteredBlocks = array_filter(
            $widgetBlocks, 
            fn (BlockTemplate $blockTemplate) => !$blockTemplate->isRendered()
        );
        
        return array_map(function(BlockTemplate $blockTemplate) {
            $html = $this->uss->twigEnvironment
                ->resolveTemplate($blockTemplate->getTemplate())
                ->render(
                    $blockTemplate->getContext() + $this->uss->twigContext
                );
            $blockTemplate->fulfilled();
            return $html;
        }, $filteredBlocks);
    }

    protected function exportWidgets(array $widgetHTMLContext): void
    {
        if(!empty($widgetHTMLContext)) {
            $nl = "\n";
            $content = $nl . implode($nl, $widgetHTMLContext) . $nl;
            $this->crudInventory->getWidgetsContainer()->setContent($content);
        }
    }
}