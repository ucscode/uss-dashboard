<?php

interface CrudActionInterface {
    public function forEachItem(array $data): CrudAction;
}