<?php

interface CrudEditSubmitCustomInterface extends CrudEditSubmitInterface
{
    /**
     * process submission for custom actions
     * 
     * @param array $data - The filtered data ready to be inserted into database
     * @return bool - Whether the data persistion was successful or not
     */
    public function onSubmit(array $data): bool;
}