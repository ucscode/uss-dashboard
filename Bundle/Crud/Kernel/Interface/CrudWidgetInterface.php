<?php

namespace Module\Dashboard\Bundle\Crud\Kernel\Interface;

use Uss\Component\Block\BlockTemplate;

interface CrudWidgetInterface
{
    public function createWidget(CrudKernelInterface $crudKernel): BlockTemplate;
}