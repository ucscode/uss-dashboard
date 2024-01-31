<?php

namespace Module\Dashboard\Bundle\Document\Interface;

use Ucscode\TreeNode\TreeNode;
use Uss\Component\Route\RouteInterface;

interface DocumentInterface
{
    public function setName(string $name): self;
    public function getName(): ?string;

    public function setRoute(?string $route, ?string $base = null): self;
    public function getRoute(): ?string;
    public function getUrl(bool $ignoreHost = false): ?string;

    public function setTemplate(?string $template, ?string $prefix = null): self;
    public function getTemplate(): ?string;

    public function setThemeIntegration(bool $inline): self;
    public function hasThemeIntegration(): bool;

    public function setThemeBaseLayout(string $layout): self;
    public function getThemeBaseLayout(): ?string;

    public function setContext(array $context): self;
    public function getContext(): array;

    public function setController(?RouteInterface $controller): self;
    public function getController(): ?RouteInterface;

    public function setRequestMethods(array $methods): self;
    public function getRequestMethods(): array;

    public function setCustom(string $key, mixed $value): self;
    public function getCustom(string $key): mixed;

    public function addMenuItem(string $name, array|TreeNode $menu, ?TreeNode $parentMenu = null): self;
    public function getMenuItem(string $name): ?TreeNode;
    public function removeMenuItem(string $name): self;
    public function getMenuItems(): array;
}