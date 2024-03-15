<?php

namespace Module\Dashboard\Bundle\Document;

use Module\Dashboard\Bundle\Document\Abstract\AbstractDocument;
use Module\Dashboard\Bundle\Document\Interface\DocumentInterface;
use Ucscode\TreeNode\TreeNode;
use Uss\Component\Common\UrlGenerator;
use Uss\Component\Route\RouteInterface;
use Uss\Component\Kernel\Uss;

class Document extends AbstractDocument implements DocumentInterface
{
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setRoute(?string $route, ?string $base = null): self
    {
        $this->route = Uss::instance()->filterContext($base . '/' . $route);
        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getUrl(bool $ignoreHost = false): ?string
    {
        return ($this->route) ? (new UrlGenerator($this->route))->getResult($ignoreHost) : null;
    }

    public function setTemplate(?string $template, ?string $prefix = null): self
    {
        $this->template = $prefix . $template;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setThemeBaseLayout(string $layout): self
    {
        $this->themeBaseLayout = $layout;
        return $this;
    }

    public function getThemeBaseLayout(): ?string
    {
        return $this->themeBaseLayout;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setController(?RouteInterface $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    public function getController(): ?RouteInterface
    {
        return $this->controller;
    }

    public function setRequestMethods(array $methods): self
    {
        $this->verifyRequestMethods($methods, __METHOD__);
        $this->requestMethods = $methods;
        return $this;
    }

    public function getRequestMethods(): array
    {
        return $this->requestMethods;
    }

    public function setCustom(string $key, mixed $value): self
    {
        $this->custom[$key] = $value;
        return $this;
    }

    public function getCustom(string $key): mixed
    {
        return $this->custom[$key] ?? null;
    }

    public function addMenuItem(string $name, array|TreeNode $menu, ?TreeNode $parentMenu = null): self
    {
        $this->discernMenuItem($menu, $parentMenu);
        $menu = is_array($menu) ? new TreeNode($name, $menu) : $menu;
        if($parentMenu && !$parentMenu->hasChild($menu)) {
            $parentMenu->addChild($name, $menu);
        }
        $this->menuItems[$name] = $menu;
        return $this;
    }

    public function getMenuItem(string $name): ?TreeNode
    {
        return $this->menuItems[$name] ?? null;
    }

    public function removeMenuItem(string $name): self
    {
        $menuItem = $this->getMenuItem($name);
        if($menuItem) {
            $menuItem->getParent()?->removeChild($menuItem);
            unset($this->menuItems[$name]);
        }
        return $this;
    }

    public function getMenuItems(): array
    {
        return $this->menuItems;
    }

    public function setThemeIntegration(bool $themeIntegration): self
    {
        $this->themeIntegration = $themeIntegration;
        return $this;
    }

    public function hasThemeIntegration(): bool
    {
        return $this->themeIntegration;
    }
}
