<?php

namespace Module\Dashboard\Bundle\Extension;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use ReflectionClass;
use Uss\Component\Kernel\Extension\ExtensionInterface;
use Uss\Component\Kernel\Resource\AccessibleMethods;
use Uss\Component\Kernel\Resource\AccessibleProperties;
use Uss\Component\Trait\SingletonTrait;

class ExtensionModel implements ExtensionInterface
{
    use SingletonTrait;
    
    protected AccessibleProperties $accessibleProperties;
    protected AccessibleMethods $accessibleMethods;
    protected AbstractDashboard $dashboard;

    public function initialize(AbstractDashboard $dashboard): void
    {
        $this->dashboard = $dashboard;
        $this->initializeAccessibleProperties();
        $this->initializeAccessibleMethods();
    }

    public function getImmutable(): array
    {
        $reflectionClass = new ReflectionClass(DashboardImmutable::class);
        return $reflectionClass->getConstants();
    }

    public function props(): AccessibleProperties
    {
        return $this->accessibleProperties;
    }

    public function meths(): AccessibleMethods
    {
        return $this->accessibleMethods;
    }

    private function initializeAccessibleProperties(): void
    {
        $properties = array_keys(get_object_vars($this->dashboard));
        $this->accessibleProperties = new AccessibleProperties($properties, $this->dashboard);
    }

    private function initializeAccessibleMethods(): void
    {
        $this->accessibleMethods = new AccessibleMethods([
                'getDocument',
                'getDocuments',
                'urlGenerator',
                'isFirewallEnabled',
                'getTheme',
            ],
            $this->dashboard
        );
    }
}