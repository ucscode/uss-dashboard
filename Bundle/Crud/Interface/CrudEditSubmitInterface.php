<?php

interface CrudEditSubmitInterface
{
    /**
     * @param array $data - The raw data that should be filtered
     * @return array - The final data that will be persisted into database
     */
    public function beforeEntry(array $data): array;

    /**
     * @param bool $status - The status of database persistion
     * @return bool - Indicating whether to allow CurdEditorManager take default action or whether
     * to do nothing so you can use your own custom action
     */
    public function afterEntry(bool $status, array $data): bool;
}
