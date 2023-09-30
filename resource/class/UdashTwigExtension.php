<?php

use Ucscode\Packages\TreeNode;

/**
 * This extension contains minified version of Udash Object
 */
final class UdashTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    public readonly TreeNode $menu;

    public function getGlobals(): array
    {
        return ['Udash' => $this];
    }

    public function __construct()
    {
        $this->menu = Udash::instance()->menu;
        Alert::flushAll();
    }

    /**
     * Uss Methods
     */
    public function getConfig(string $property)
    {
        return Udash::instance()->getConfig($property);
    }

    public function linkTo(string $path = '', array $param = []): string
    {
        return Udash::instance()->urlGenerator($path, $param)->getResult();
    }

}
