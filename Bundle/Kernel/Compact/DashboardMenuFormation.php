<?php

namespace Module\Dashboard\Bundle\Kernel\Compact;

use RuntimeException;
use Ucscode\TreeNode\TreeNode;
use Ucscode\UssElement\UssElement;

class DashboardMenuFormation
{
    public function __construct(protected TreeNode $menu)
    {}

    public function beginProcess(?callable $func = null): void
    {
        $this->menu->traverseChildren(function(TreeNode $node) use ($func) {
            $this->verifyLabel($node);
            $func ? call_user_func($func, $node) : $this->localConcept($node);
            $this->concludeSetup($node);
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

    public function concludeSetup(TreeNode $node): void
    {
        $node->sortChildren(function (TreeNode $a, TreeNode $b) {
            $sortA = $a->getAttribute('order') ?? 0;
            $sortB = $b->getAttribute('order') ?? 0;
            return (int)$sortA <=> (int)$sortB;
        });

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
}
