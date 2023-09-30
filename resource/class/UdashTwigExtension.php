<?php

use Ucscode\Packages\TreeNode;

/**
 * This extension contains minified version of Udash Object
 */
final class UdashTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;

    public function getGlobals(): array
    {
        return ['Udash' => $this];
    }

    public function __construct()
    {
        $udash = Udash::instance();
        $this->menu = $udash->menu;
        $this->userMenu = $udash->userMenu;
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

    /**
     * Get Notifications from database
     */
    public function getNotifications(array $data) {
        //return new PushNotification();
    }

}
