<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Service\AppControl;
use Module\Dashboard\Bundle\Kernel\Service\AppFactory;
use Module\Dashboard\Foundation\DocumentController;
use RuntimeException;
use Ucscode\TreeNode\TreeNode;
use Ucscode\UssElement\UssElement;
use Uss\Component\Event\Event;
use Uss\Component\Route\Route;

abstract class AbstractDashboardCentral implements DashboardInterface
{
    public readonly AppControl $appControl;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;
    protected bool $firewallEnabled = true;
    protected array $documents = [];

    /**
     * Set Initial values such as base(route), theme, user permission etc
     * Note: child class should override this method but still call it
     * parent::createProject($config);
     */
    public function createApp(AppControl $appControl): void
    {
        $this->appControl = $appControl;
        AppFactory::registerApp($this);
        $this->menu = new TreeNode('Main Menu');
        $this->userMenu = new TreeNode('User Menu');
        (new Event())->addListener('modules:loaded', fn () => $this->createGUI(), -10);
    }

    /**
     * @method iterateMenu
     */
    protected function synchronizeMenu(TreeNode $menu, ?callable $func = null): void
    {
        $menu->synchronizeChildren($menu->getChildren(), function (TreeNode $item) use ($menu, $func): mixed {

            if(empty($item->getAttribute('label'))) {
                throw new RuntimeException(
                    sprintf(
                        "Error in %s (%s): The item '%s' is missing a required 'label' attribute.",
                        $menu->getName(),
                        $item->getName(),
                        $item->getIdentity()
                    )
                );
            };

            $result = $func ? $func($item) : $this->inlineMenu($item);

            $item->sortChildren(function (TreeNode $a, TreeNode $b) {
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
                if(empty($item->getAttribute($key))) {
                    $item->setAttribute($key, $value);
                };
            }

            return $result;

        });
    }

    /**
     * Process the GUI/Theme for the created dashboard Application
     */
    private function createGUI(): void
    {
        foreach($this->getDocuments() as $document) {
            foreach(array_filter($document->getMenuItems(true)) as $name => $parentItem) {
                if(!$parentItem->getChild($name)) { // get relative child item
                    $parentItem->addChild($name, $document->getMenuItem($name));
                }
            };
        }

        $this->synchronizeMenu($this->menu);
        $this->synchronizeMenu($this->userMenu);

        foreach($this->getDocuments() as $document) {
            if($document->getRoute() !== null) {
                new Route(
                    $document->getRoute(),
                    new DocumentController($this, $document),
                    $document->getRequestMethods()
                );
            }
        }
    }

    /**
     * @method defaultMenuAttributes
     */
    private function inlineMenu(TreeNode $item): void
    {
        $anchor = $item->getAttribute('href');
        $children = $item->getChildren();

        if(!empty($children) && !is_null($anchor)) {

            $icon = (new UssElement(UssElement::NODE_I))
                ->setAttribute('class', 'bi bi-pin-angle ms-1 menu-pin');
            $label = $item->getAttribute('label') . $icon->getHTML();

            $item->addChild($item->name, [
                'label' => $label,
                'href' => $anchor,
                'order' => -1024,
                'pinned' => true,
                'active' => $item->getAttribute('active'),
                'target' => $item->getAttribute('target') ?? '_self',
            ]);
        }
    }
}
