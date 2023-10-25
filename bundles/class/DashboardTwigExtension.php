<?php

use Ucscode\Packages\TreeNode;

final class DashboardTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    private Uss $uss;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;
    public readonly string $themeName;
    public readonly string $themePath;
    public readonly string $themeNamespace;
    public readonly string $themeUrl;

    public function getGlobals(): array
    {
        return ['DashboardExtension' => $this];
    }

    public function __construct(
        private AbstractDashboard $dashboard
    ) {
        $this->setInitialValues();
        Alert::flushAll();
    }

    /**
     * @method getDashboardProperty
     */
    public function getDashboardProperty(string $property): mixed
    {
        return $this->dashboard->{$property} ?? null;
    }

    /**
     * @method themePath
     */
    public function themePath(string $path = ''): string
    {
        $path = $this->uss->filterContext($path);
        $themePath = DashboardImmutable::THEME_DIR . '/' . $this->themeName;
        if(!empty($path)) {
            $themePath .= '/' . $path;
        };
        return $themePath;
    }

    /**
     * @method themeUrl
     */
    public function themeUrl(string $path = ''): string
    {
        return $this->uss->abspathToUrl($this->themePath($path));
    }

    /**
     * @method getAttribute
     */
    public function getAttribute(string $property)
    {
        return $this->dashboard->getAttribute($property);
    }

    /**
     * @method urlGenerator
     */
    public function urlGenerator(string $path = '', array $param = []): string
    {
        return $this->dashboard->urlGenerator($path, $param)->getResult();
    }

    /**
     * @method getArchiveUrl
     */
    public function getArchiveUrl(string $pagename): ?string
    {
        return $this->dashboard->getArchiveUrl($pagename);
    }

    /**
     * @method setInitialValues
     */
    public function setInitialValues(): void
    {
        $this->uss = Uss::instance();
        $this->themeName = $this->dashboard->config->getTheme();
        $this->themeNamespace = '@Theme/' . $this->themeName;
    }
}
