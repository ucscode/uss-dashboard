<?php

final class DashboardTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    private Uss $uss;
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
     * @method getAttribute
     */
    public function getDashboardAttribute(string $property)
    {
        return $this->dashboard->getAttribute($property);
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
     * @method urlGenerator
     */
    public function urlGenerator(string $path = '', array $param = [], $base = ''): string
    {
        if(!empty($base)) {
            $urlGenerator = new UrlGenerator($path, $param, $base);
        } else {
            $urlGenerator = $this->dashboard->urlGenerator($path, $param);
        }
        return $urlGenerator->getResult();
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
    private function setInitialValues(): void
    {
        $this->uss = Uss::instance();
        $this->themeName = $this->dashboard->config->getTheme();
        $this->themeNamespace = '@Theme/' . $this->themeName;
    }
}
