<?php

use Ucscode\Packages\TreeNode;

final class DashboardTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
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
        $uss = Uss::instance();
        $this->menu = $this->dashboard->menu;
        $this->userMenu = $this->dashboard->userMenu;
        $this->themeName = $this->dashboard->config->theme;
        $this->themePath = DashboardImmutable::THEME_DIR . '/' . $this->themeName;
        $this->themeNamespace = '@Theme/' . $this->themeName;
        $this->themeUrl = $uss->abspathToUrl($this->themePath);
    }
}
