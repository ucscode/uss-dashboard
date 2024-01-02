<?php

namespace Module\Dashboard\Bundle\Kernel\Interface;

interface DashboardFormInterface
{
    public function addBuilderAction(string $name, DashboardFormBuilderInterface $exporter): self;

    public function removeBuilderAction(string $name): self;

    // Handle Form Submission
    public function handleSubmission(): void;

    // Apply logic to check if the form has been submitted.
    public function isSubmitted(): bool;

    // Apply logic to check if form submission is from a trusted source.
    public function isTrusted(): bool;

    // Apply logic to collect on relevant data from _POST or _GET request.
    public function filterData(): array;

    // Apply logic to check if filtered contents are valid
    public function isValid(array $data): bool;

    // Apply logic for saving the provided data into database
    public function persistEntry(array $data): bool;

    // This should be called when entity persistion is successful
    public function onEntrySuccess(array $data): void;

    // This should be called when entity persistion is unsuccessful
    public function onEntryFailure(array $data): void;

    public function resolveInvalidRequest(?array $data): void;

    public function resolveUntrustedRequest(): void;

}
