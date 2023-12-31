<?php

namespace Module\Dashboard\Bundle\Extension;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Uss\Component\Kernel\Uss;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use Module\Dashboard\Bundle\Kernel\Enumerator;
use ReflectionClass;
use Uss\Component\Manager\UrlGenerator;

final class DashboardExtension extends AbstractExtension implements GlobalsInterface
{
    private Uss $uss;
    public readonly array $immutable;
    public readonly array $ENUM;

    public function getGlobals(): array
    {
        return ['__dashboard' => $this];
    }

    public function __construct(private AbstractDashboard $dashboard)
    {
        $immutable = new ReflectionClass(DashboardImmutable::class);
        $this->immutable = $immutable->getConstants();
        $this->ENUM = array_column(Enumerator::cases(), null, 'name');
    }

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
