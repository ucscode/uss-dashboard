<?php

interface DashboardInterface
{
    public function createProject(DashboardConfig $config): void;
    public function setAttribute(string $property, mixed $value): void;
    public function getAttribute(?string $property): mixed;
    public function removeAttribute(string $property): void;
    public function enableFirewall(bool $enable = true): void;
    public function render(string $template, array $options = []): void;
    public function isActiveBase(): bool;
    public function urlGenerator(string $path = '/', array $queries = []): UrlGenerator;
    public function getPageManagerUrl(string $name): ?string;
    public function isFirewallEnabled(): bool;
    public function useTheme(string $theme): string;
}
