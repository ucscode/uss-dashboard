<?php

abstract class AbstractDashboard extends AbstractDashboardComposition
{
    /**
     * @method isActive
     */
    public function isActive(): bool
    {
        $uss = Uss::instance();
        $regex = '/^' . $this->config->getBase() . '(?!\w)/is';
        $request = $uss->filterContext($uss->splitUri());
        return preg_match($regex, $request);
    }

    /**
     * @method getPageManagerUrl
     */
    public function getPageManagerUrl(string $name): ?string
    {
        $pageManager = $this->pageRepository->getPageManager($name);
        if($pageManager) {
            $urlGenerator = $this->urlGenerator($pageManager->getRoute() ?? '');
            return $urlGenerator->getResult();
        }
        return null;
    }

    /**
     * @method urlGenerator
     */
    public function urlGenerator(string $path = '/', array $query = []): UrlGenerator
    {
        $urlGenerator = new UrlGenerator($path, $query, $this->config->getBase());
        return $urlGenerator;
    }

    /**
     * @method setAttribute
     */
    public function setAttribute(?string $property = null, mixed $value = null): void
    {
        $this->attributes[$property] = $value;
    }

    /**
     * @method getAttribute
     */
    public function getAttribute(?string $property = null): mixed
    {
        if(is_null($property)) {
            return $this->attributes;
        };
        return $this->attributes[$property] ?? null;
    }

    /**
     * @method removeAttribute
     */
    public function removeAttribute(string $property): void
    {
        if(array_key_exists($property, $this->attributes)) {
            unset($this->attributes[$property]);
        };
    }

    /**
     * @method enableFirewall
     */
    public function enableFirewall(bool $enable = true): void
    {
        $this->firewallEnabled = $enable;
    }

    /**
     * @method firewallEnabled
     */
    public function isFirewallEnabled(): bool
    {
        return $this->firewallEnabled;
    }

    /**
     * Override this method and change the logic class if you are
     * not satisified with the system built-in logic
     * @method render
     */
    public function render(string $template, array $options = []): void
    {
        (new Event())->addListener(
            'dashboard:render',
            new DashboardRenderLogic(
                $this,
                Uss::instance(),
                $template,
                $options
            )
        );
    }

    /**
     * @method useTheme
     */
    public function useTheme(string $template): string
    {
        $theme = $this->config->getTheme();
        $dymanicTemplate = "@Theme/{$theme}/{$template}";
        return Uss::instance()->filterContext($dymanicTemplate);
    }
}
