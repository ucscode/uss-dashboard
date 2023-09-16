<?php

final class UdashTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    public function getGlobals(): array
    {
        return [
            'Udash' => new class () {
                public $user;

                public function __construct()
                {
                    $this->user = Udash::instance()->user;
                }

                public function getConfig(string $property)
                {
                    return Udash::instance()->config($property);
                }

            }
        ];
    }
}