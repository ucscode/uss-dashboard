<?php

namespace Module\Dashboard\Bundle\Common;

use Ucscode\TreeNode\TreeNode;
use Uss\Component\Manager\UrlGenerator;
use Uss\Component\Route\RouteInterface;
use Uss\Component\Kernel\Uss;

class Document
{
    protected ?string $name = null;
    protected ?string $route = null;
    protected ?string $template = null;
    protected ?RouteInterface $controller = null;
    protected array $menuItems = [];
    protected array $custom = [];
    protected array $requestMethods = ['GET', 'POST'];

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @method setRoute
     */
    public function setRoute(?string $route, ?string $base = null): self
    {
        $this->route = Uss::instance()->filterContext($base . $route, false);
        return $this;
    }

    /**
     * @method getRoute
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * Get Url based on the current route
     */
    public function getUrl(bool $ignoreHost = false): ?string
    {
        if($this->route) {
            return (new UrlGenerator($this->route))->getResult($ignoreHost);
        };
        return null;
    }

    /**
     * @method setTemplate
     */
    public function setTemplate(?string $template, ?string $prefix = null): self
    {
        $this->template = $prefix . $template;
        return $this;
    }

    /**
     * @method getTemplate
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @method setController
     */
    public function setController(?RouteInterface $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @method getController
     */
    public function getController(): ?RouteInterface
    {
        return $this->controller;
    }

    /**
     * @method setRequestMethods
     */
    public function setRequestMethods(array $methods): self
    {
        $this->validateMethod($methods, __METHOD__);
        $this->requestMethods = $methods;
        return $this;
    }

    /**
     * @method getRequestMethods
     */
    public function getRequestMethods(): array
    {
        return $this->requestMethods;
    }

    /**
     * @method setCustom
     */
    public function setCustom(string $key, mixed $value): self
    {
        $this->custom[$key] = $value;
        return $this;
    }

    /**
     * @method getCustom
     */
    public function getCustom(string $key): mixed
    {
        return $this->custom[$key];
    }

    /**
     * @method addMenuItem
     */
    public function addMenuItem(string $name, array|TreeNode $menu, TreeNode $parentMenu): self
    {
        if ($parentMenu === $menu) {
            throw new \Exception(
                sprintf(
                    '%1$s (%2$s in #argument 2) cannot be equivalent to (%2$s in #argument 3)',
                    __METHOD__,
                    TreeNode::class
                )
            );
        }
        $menu = is_array($menu) ? new TreeNode($name, $menu) : $menu;
        $this->menuItems[$name] = [
            'item' => $menu,
            'parent' => $parentMenu,
        ];
        return $this;
    }

    /**
     * @method getMenuItem
     */
    public function getMenuItem(string $name, bool $onlyItem = false): array|TreeNode|null
    {
        $items = $this->menuItems[$name] ?? null;
        return ($items && $onlyItem) ? $items['item'] : $items;
    }

    /**
     * @method getMenuItems
     */
    public function getMenuItems(): array
    {
        return $this->menuItems;
    }

    public function __debugInfo(): array
    {
        return [
            'name' => $this->name,
            'route' => $this->route,
            'template' => $this->template,
            'controller' => $this->controller ? '(Instance): ' . $this->controller::class : null,
            'menuItems' => '(' . count($this->menuItems) . ' Instance): ' . TreeNode::class,
            'requestMethods' => '(Array) => [' . implode(", ", $this->requestMethods) . ']',
            'custom' => $this->custom,
        ];
    }

    private function validateMethod(array $method, string $caller): void
    {
        $methods = [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE'
        ];

        $method = array_values(array_map(fn ($value) => strtoupper(trim($value)), $method));

        foreach(array_diff($method, $methods) as $method) {
            throw new \Exception(
                sprintf("Invalid Request Method '%s' provided in argument 2 of %s('method', ...) ", $method, $caller)
            );
        };
    }
}
