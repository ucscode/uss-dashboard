<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Interface;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FieldPedigree;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;

interface FormManagerInterface
{
    public function getForm(): Form;
    public function getFieldPedigree(string|Field $context): ?FieldPedigree;
    public function configureField(string $name, array $context): ?Field;
}