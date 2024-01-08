<?php

namespace Module\Dashboard\Bundle\Extension;

use Module\Dashboard\Bundle\Common\Document;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use ReflectionClass;
use Uss\Component\Manager\UrlGenerator;
use Uss\Component\Kernel\Enumerator;

final class DashboardExtension extends AbstractExtension implements GlobalsInterface
{
    public readonly array $immutable;

    public function getGlobals(): array
    {
        return ['__dashboard' => $this];
    }

    public function __construct(private AbstractDashboard $dashboard)
    {
        $immutable = new ReflectionClass(DashboardImmutable::class);
        $this->immutable = $immutable->getConstants();
    }

    public function props(): array
    {
        return get_object_vars($this->dashboard);
    }

    public function meths(): AccessibleMethods
    {
        return new AccessibleMethods($this->dashboard);
    }
}
