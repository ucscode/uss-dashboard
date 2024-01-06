<?php

namespace Module\Dashboard\Bundle\Kernel\Interface;

interface DashboardFormInterface
{
    public function build(): void;
    public function addBuilderAction(string $name, DashboardFormBuilderInterface $exporter): self;
    public function getBuilderAction(string $name): ?DashboardFormBuilderInterface;
    public function removeBuilderAction(string $name): self;
    public function addSubmitAction(string $name, DashboardFormSubmitInterface $submitter): self;
    public function getSubmitAction(string $name): ?DashboardFormSubmitInterface;
    public function removeSubmitAction(string $name): self;
    public function handleSubmission(): void;
    public function isSubmitted(): bool;
    public function filterResource(): array;
}
