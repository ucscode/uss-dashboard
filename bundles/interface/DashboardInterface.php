<?php

interface DashboardInterface
{
    public function setAttribute(string $property, mixed $value): void;
    public function getAttribute(?string $property): mixed;
    public function removeAttribute(string $property): void;
    public function enableFirewall(bool $enable = true): void;
    public function render(string $template, array $options = []): void;
}
