<?php

namespace Module\Dashboard\Bundle\Extension;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use ReflectionClass;
use Uss\Component\Kernel\Extension\ExtensionInterface;
use Uss\Component\Kernel\Resource\AccessibleMethods;
use Uss\Component\Kernel\Resource\AccessibleProperties;

final class DashboardExtension extends AbstractExtension implements GlobalsInterface, ExtensionInterface
{
    public readonly array $immutable;
    protected AccessibleProperties $accessibleProperties;
    protected AccessibleMethods $accessibleMethods;

    public function getGlobals(): array
    {
        return ['__dashboard' => $this];
    }

    public function __construct(private AbstractDashboard $dashboard)
    {
        $this->immutable = (new ReflectionClass(DashboardImmutable::class))->getConstants();
        $this->initializeAccessibleProperties();
        $this->initializeAccessibleMethods();
    }

    public function props(): AccessibleProperties
    {
        return $this->accessibleProperties;
    }

    public function meths(): AccessibleMethods
    {
        return $this->accessibleMethods;
    }

    protected function initializeAccessibleProperties(): void
    {
        $properties = array_keys(get_object_vars($this->dashboard));
        $this->accessibleProperties = new AccessibleProperties($this->dashboard, $properties);
    }

    protected function initializeAccessibleMethods(): void
    {
        $this->accessibleMethods = new AccessibleMethods($this->dashboard, [
            'getDocument',
            'getDocuments',
            'urlGenerator',
            'isFirewallEnabled',
            'getTheme',
        ]);
    }
}
