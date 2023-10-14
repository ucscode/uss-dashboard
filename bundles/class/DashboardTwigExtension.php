<?php

use Ucscode\Packages\TreeNode;

final class DashboardTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;

    public function getGlobals(): array
    {
        return ['Ud' => $this];
    }

    public function __construct(
        private DashboardInterface $dashboard
    )
    {
        $this->menu = $this->dashboard->menu;
        $this->userMenu = $this->dashboard->userMenu;
        Alert::flushAll();
    }

    /**
     * Uss Methods
     */
    public function getAttribute(string $property)
    {
        return $this->dashboard->getAttribute($property);
    }

    public function urlGenerator(string $path = '', array $param = []): string
    {
        return $this->dashboard->urlGenerator($path, $param)->getResult();
    }

    public function getArchiveUrl(string $pagename): ?string
    {
        return $this->dashboard->getArchiveUrl($pagename);
    }

}
