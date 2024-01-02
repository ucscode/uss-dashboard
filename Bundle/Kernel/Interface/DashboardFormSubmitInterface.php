<?php

namespace Module\Dashboard\Bundle\Kernel\Interface;

interface DashboardFormSubmitInterface
{
    public function onSubmit(array $filteredResource): void;
    public function onValidateResource(array $validatedResource): void;
    public function onPersistResource(int|array|bool $response): void;
}