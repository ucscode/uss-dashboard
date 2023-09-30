<?php

/**
 * This extension contains minified version of Udash Object
 */
final class UdashTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    public function getGlobals(): array
    {
        return ['Udash' => $this];
    }

    public function __construct()
    {
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
