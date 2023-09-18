<?php

interface UdashFormInterface
{
    /**
     * Process the form data
     *
     * @return self The registration form object
     */
    public function handleSubmission();

    /**
     * When data is successfully inserted into database
     *
     * This method should be called by `handleSubmission()` when the form submission is successful
     *
     * @param array $post The data inserted into database.
     *
     * @return void
     */
    public function onDataEntrySuccess(array $data, bool $isUpdate = false);

    /**
     * When data fails to insert into database
     *
     * Handles actions to be taken upon encountering form submission errors.
     *
     * This method should be called by `handleSubmission()` when there are errors in the form submission.
     *
     * @param array $post The POST data submitted with the form.
     *
     * @return void
     */
    public function onDataEntryFailure(array $data, bool $isUpdate = false);

    /**
     * Refactor the data obtained from $_POST or $_GET.
     *
     * This method retrieves and refactors the data necessary for querying the database.
     *
     * @param bool $sanitize Indicates whether to sanitize the data.
     *
     * @return array The filtered and refactored data.
     */
    public function getFilteredData(bool $sanitize): array;

    /**
     * Get the URL for a specific page route.
     *
     * @param string $pagename The name of the page.
     *
     * @return string|null The URL for the specified page route, or null if not found.
     */
    public function getRouteUrl(string $pagename): ?string;

    /**
     * Check if the form has been submitted.
     *
     * @return bool true if the form has been submitted, otherwise false.
     */
    public function isSubmitted(): bool;

    /**
     * Check if the form submission is trusted.
     *
     * Ensures that the form is not subjective to CSRF, DDOS, BruteForce or other similar attract
     *
     * @return bool true if the form submission is trusted, otherwise false.
     */
    public function isTrusted(): bool;

    /**
     * Redirect to a specified location upon successful form submission.
     *
     * @param string $location The URL to which to redirect.
     *
     * @return void
     */
    public function redirectOnSuccessTo(string $location): void;

    /**
     * Check the validity of a set of data against defined rules.
     *
     * This method examines the provided data to determine if it adheres to predefined validation rules.
     *
     * @param array $data The data to be validated.
     *
     * @return bool true if the data is valid according to the defined rules, otherwise false.
     */
    public function isValid(?array $data): bool;

}
