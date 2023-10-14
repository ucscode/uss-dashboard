<?php

interface DashboardFormInterface
{
    // Handle Form Submission
    public function handleSubmission(): void;

    // Check if the form has been submitted.
    public function isSubmitted(): bool;

    // Check if form submission is from a reliable source.
    public function isTrusted(): bool;

    // Retrieve only relevant data from _POST or _GET request.
    public function extractRelevantData(): array;

    // Check if retrieved data is valid
    public function isValid(array $data): bool;

    // Responsible for saving the provided data to the database
    public function persistEntry(array $data): bool;

    // Called when entity persistion is successful
    public function onEntrySuccess(array $data): void;

    // Called when entity persistion is unsuccessful
    public function onEntryFailure(array $data): void;

}
