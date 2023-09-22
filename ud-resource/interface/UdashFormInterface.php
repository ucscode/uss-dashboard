<?php

interface UdashFormInterface
{
    /**
     * Process the form data
     *
     * @return self The registration form object
     */
    public function handleSubmission(): void;

    /** Persist an entry by saving data to the database.
    *
    * This method is responsible for saving the provided data to the database. It should perform
    * the necessary operations to store the data securely and return a boolean value indicating
    * whether the operation was successful or not.
    *
    * @param array $data The data to be saved to the database.
    *
    * @return bool Returns true if the data was successfully saved to the database; otherwise, false.
    */
    public function persistEntry(array $data): bool;

    /**
     * When data is successfully inserted into database
     *
     * This method should be called by `handleSubmission()` when the form submission is successful
     *
     * @param array $post The data inserted into database.
     *
     * @return void
     */
    public function onEntrySuccess(array $data): void;

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
    public function onEntryFailure(array $data): void;

    /**
     * This method should filter and retrieves only necessary data from _POST or _GET request.
     *
     * @param bool $sanitize Indicates whether to sanitize the data.
     *
     * @return array The filtered and refactored data.
     */
    public function getFilteredSubmissionData(bool $sanitize): array;

    /**
     * This method should filter and retrieves only the data necessary for inserting or update database record.
     *
     * @param array The data to filter
     *
     * @return array The filtered and persistable data.
     */
    public function prepareEntryData(array $data): array;

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
