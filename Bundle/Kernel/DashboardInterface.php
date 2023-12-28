<?php

namespace Module\Dashboard\Bundle\Kernel;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\User\User;
use Uss\Component\Manager\UrlGenerator;

interface DashboardInterface
{
    public function createApp(AppControl $appControl): void;
    public function render(string $template, array $options = []): void;
    public function addDocument(string $name, Document $document): self;
    public function getDocument(string $name): ?Document;
    public function removeDocument(string $name): self;
    public function getDocuments(): array;
    public function enableFirewall(bool $enable = true): self;
    public function isFirewallEnabled(): bool;
    public function urlGenerator(string $path = '/', array $queries = []): UrlGenerator;
    public function getCurrentUser(): ?User;
    public function getTheme(string $path, Enumerator $enum): string;
}
