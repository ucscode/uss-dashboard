<?php

namespace Module\Dashboard\Bundle\Kernel\Compact;

use RuntimeException;
use Ucscode\TreeNode\TreeNode;
use Ucscode\UssElement\UssElement;

class DashboardMenuFormation
{
    public function __construct(protected TreeNode $menu, ?callable $func = null)
    {
        $this->beginProcess($func);
    }

    protected function beginProcess(?callable $func): void
    {
        $this->applySortAlgorithm($this->menu);
        $this->menu->traverseChildren(function(TreeNode $node) use ($func) {
            $this->verifyLabel($node);
            $func ? call_user_func($func, $node) : $this->localConcept($node);
            $this->updateAttributes($node);
            $this->applySortAlgorithm($node);
            $this->activateAncestors($node);
        });
    }

    protected function verifyLabel(TreeNode $node): void
    {
        if(empty($node->getAttribute('label'))) {
            throw new RuntimeException(
                "Menu Item is missing a required 'label' attribute."
            );
        };
    }

    protected function localConcept(TreeNode $node): void
    {
        $anchor = $node->getAttribute('href');
        $children = $node->getChildren();

        if(!empty($children) && !is_null($anchor)) {

            $icon = new UssElement(UssElement::NODE_I);
            $icon->setAttribute('class', 'bi bi-pin-angle ms-1 menu-pin');
            $label = $node->getAttribute('label') . $icon->getHTML();

            $node->addChild($node->name, [
                'label' => $label,
                'href' => $anchor,
                'order' => -1024,
                'pinned' => true,
                'active' => $node->getAttribute('active'),
                'target' => $node->getAttribute('target') ?? '_self',
            ]);
        }
    }

    protected function updateAttributes(TreeNode $node): void
    {
        $attributes = [
            'target' => '_self',
            'href' => 'javascript:void(0)',
            'pinned' => false,
        ];
        foreach($attributes as $key => $value) {
            if(empty($node->getAttribute($key))) {
                $node->setAttribute($key, $value);
            };
        }
    }

    protected function applySortAlgorithm(TreeNode $node): void
    {
        $node->sortChildren(function (TreeNode $a, TreeNode $b) {
            $orderA = $a->getAttribute('order') ?? 0;
            $orderB = $b->getAttribute('order') ?? 0;
            return (float)$orderA <=> (float)$orderB;
        });
    }

    protected function activateAncestors(TreeNode $node): void
    {
        if($node->getAttribute('active')) {
            foreach($node->getParents() as $ancestor) {
                $ancestor->setAttribute('active', true);
            }
        }
    }
}
