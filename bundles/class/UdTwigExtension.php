<?php

use Ucscode\Packages\TreeNode;

/**
 * This extension contains minified version of Ud Object
 */
final class UdTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;

    public function getGlobals(): array
    {
        return ['Ud' => $this];
    }

    public function __construct()
    {
        $ud = Ud::instance();

        $this->menu = $ud->menu;
        $this->userMenu = $ud->userMenu;

        Alert::flushAll();
    }

    /**
     * Uss Methods
     */
    public function getConfig(string $property)
    {
        return Ud::instance()->getConfig($property);
    }

    public function urlGenerator(string $path = '', array $param = []): string
    {
        return Ud::instance()->urlGenerator($path, $param)->getResult();
    }

    public function getArchiveUrl(string $pagename): ?string
    {
        return Ud::instance()->getArchiveUrl($pagename);
    }

}
