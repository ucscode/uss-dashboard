<?php

namespace Module\Dashboard\Bundle\Kernel\Interface;

interface DashboardFormInterface
{
    public function build(): void;
    
    public function addBuilderAction(string $name, DashboardFormBuilderInterface $exporter): self;

    public function removeBuilderAction(string $name): self;

    // Handle Form Submission
    public function handleSubmission(): void;

    // Apply logic to check if the form has been submitted.
    public function isSubmitted(): bool;

    // Apply logic to collect on relevant data from _POST or _GET request.
    public function filterResource(): array;

    // Apply logic to check if filtered contents are valid
    public function validateResource(array $resource): array|bool|null;

    // Apply logic for saving the provided data into database
    public function persistResource(array $resource): bool;
}
