<?php
/**
 * This is one
 */
final class UdashTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    public function getGlobals(): array
    {
        return array('Udash' => $this);
    }

    public function __construct()
    {
        Alert::flushAll();
    }

    public function getConfig(string $property)
    {
        return Udash::instance()->getConfig($property);
    }
}
