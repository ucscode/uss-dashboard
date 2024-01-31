<?php

namespace Module\Dashboard\Bundle\Document\Abstract;

use Module\Dashboard\Bundle\Exception\DashboardException;
use Ucscode\TreeNode\TreeNode;
use Uss\Component\Route\RouteInterface;

abstract class AbstractDocument
{
    protected bool $themeIntegration = true;
    protected ?string $name = null;
    protected ?string $route = null;
    protected ?string $template = null;
    protected ?string $themeBaseLayout = null;
    protected array $context = [];
    protected array $menuItems = [];
    protected array $custom = [];
    protected array $requestMethods = ['GET', 'POST'];
    protected ?RouteInterface $controller = null;

    public function __debugInfo(): array
    {
        return [
            'custom:__debugInfo' => true,
            'name' => $this->name,
            'route' => $this->route,
            'template' => $this->template,
            'controller' => $this->controller ? '(Instance of): ' . $this->controller::class : null,
            'menuItems' => '(' . count($this->menuItems) . ' Instance of): ' . TreeNode::class,
            'requestMethods' => '(Array) => [' . implode(", ", $this->requestMethods) . ']',
            'context' => $this->context,
            'custom' => $this->custom,
        ];
    }

    protected function verifyRequestMethods(array $method, string $caller): void
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
            throw new DashboardException(
                sprintf("Invalid Request Method '%s' provided in argument 2 of %s('method', ...) ", $method, $caller)
            );
        };
    }

    protected function discernMenuItem(array|TreeNode $menu, ?TreeNode $parentMenu): void
    {
        if ($parentMenu === $menu) {
            throw new DashboardException(
                sprintf(
                    '%1$s (%2$s in #argument 2) cannot be equivalent to (%2$s in #argument 3)',
                    __METHOD__,
                    TreeNode::class
                )
            );
        }
    }
}