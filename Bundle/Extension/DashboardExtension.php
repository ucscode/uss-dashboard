<?php

namespace Module\Dashboard\Bundle\Extension;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use Module\Dashboard\Bundle\Kernel\Enumerator;
use ReflectionClass;
use Uss\Component\Kernel\Uss;
use Uss\Component\Manager\UrlGenerator;

final class DashboardExtension extends AbstractExtension implements GlobalsInterface
{
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

    /**
     * Convert a namespace path to file system path or URL
     */
    public function scope(?string $templatePath = Uss::NAMESPACE, Enumerator $enum = Enumerator::FILE_SYSTEM, int $index = 0): string
    {
        $uss = Uss::instance();
        $templatePath = $uss->filterContext($templatePath);
        if(!preg_match('/^@\w+/i', $templatePath)) {
            $templatePath = '@' . Uss::NAMESPACE . '/' . $templatePath;
        }

        $context = explode("/", $templatePath);
        $namespace = str_replace('@', '', array_shift($context));
        $filesystem = $uss->filesystemLoader->getPaths($namespace)[$index] ?? null;
        $prefix = '';

        if($filesystem) {
            $prefix = match($enum) {
                Enumerator::FILE_SYSTEM => $filesystem,
                Enumerator::THEME => "@{$namespace}",
                default => $uss->pathToUrl($filesystem)
            };
        }

        return $prefix . '/' . $uss->filterContext(implode('/', $context));
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
