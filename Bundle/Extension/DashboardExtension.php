<?php

namespace Module\Dashboard\Bundle\Extension;

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

    /**
     * Get a path from the current theme and return value as file system or URL
     */
    public function theme(string $path, Enumerator $enum = Enumerator::THEME): string
    {
        return $this->dashboard->getTheme($path, $enum);
    }

    /**
     * @method urlGenerator
     */
    public function urlGenerator(string $path = '', array $param = [], ?string $base = null): string
    {
        if(!is_null($base)) {
            $urlGenerator = new UrlGenerator($path, $param, $base);
        } else {
            $urlGenerator = $this->dashboard->urlGenerator($path, $param);
        }
        return $urlGenerator->getResult();
    }
}
