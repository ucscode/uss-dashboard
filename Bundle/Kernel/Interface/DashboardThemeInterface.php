<?php

namespace Module\Dashboard\Bundle\Kernel\Interface;

interface DashboardThemeInterface
{
    public function loadDocuments(array $documents): void;
}