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
        private AbstractDashboard $dashboard
    ) {
        $this->menu = $this->dashboard->menu;
        $this->userMenu = $this->dashboard->userMenu;
        Alert::flushAll();
    }

    public function getDashboardProperty(string $property): mixed
    {
        return $this->dashboard->{$property} ?? null;
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
