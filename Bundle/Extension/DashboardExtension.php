<?php

namespace Module\Dashboard\Bundle\Extension;

use Module\Dashboard\Bundle\Common\Document;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use ReflectionClass;
use Ucscode\TreeNode\TreeNode;
use Uss\Component\Manager\UrlGenerator;
use Uss\Component\Kernel\Enumerator;

final class DashboardExtension extends AbstractExtension implements GlobalsInterface
{
    public readonly array $immutable;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;

    public function getGlobals(): array
    {
        return ['__dashboard' => $this];
    }

    public function __construct(private AbstractDashboard $dashboard)
    {
        $immutable = new ReflectionClass(DashboardImmutable::class);
        $this->immutable = $immutable->getConstants();
        $this->menu = $dashboard->menu;
        $this->userMenu = $dashboard->userMenu;
    }

    /**
     * Get a path from the current theme and return value as file system or URL
     */
    public function getTheme(string $path, Enumerator $enum = Enumerator::THEME): string
    {
        return $this->dashboard->getTheme($path, $enum);
    }

    /**
     * @method urlGenerator
     */
    public function urlGenerator(string $path = '', array $param = [], ?string $base = null): string
    {
        $urlGenerator = !is_null($base) ?
            new UrlGenerator($path, $param, $base) :
            $this->dashboard->urlGenerator($path, $param);
        return $urlGenerator->getResult();
    }

    /**
     * @method getDocument
     */
    public function getDocument(string $name): ?Document
    {
        return $this->dashboard->getDocument($name);
    }
}
