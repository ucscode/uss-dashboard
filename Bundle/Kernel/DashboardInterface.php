<?php

namespace Module\Dashboard\Bundle\Kernel;

interface DashboardInterface
{
    public function createApp(AppControl $appControl): void;
    public function setAttribute(string $property, mixed $value): void;
    public function getAttribute(?string $property): mixed;
    public function removeAttribute(string $property): void;
    public function enableFirewall(bool $enable = true): void;
    public function render(string $template, array $options = []): void;
    public function isActive(): bool;
    public function urlGenerator(string $path = '/', array $queries = []): UrlGenerator;
    public function getPageManagerUrl(string $name): ?string;
    public function isFirewallEnabled(): bool;
    public function useTheme(string $theme): string;
    public function getCurrentUser(): ?User;
}
