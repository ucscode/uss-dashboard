<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Interface;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FieldPedigree;
use Ucscode\UssForm\Field\Field;

interface FormManagerInterface
{
    public function getForm(): CrudEditorForm;
    public function getFieldPedigree(string|Field $context): ?FieldPedigree;
    public function configureField(string $name, array $context): ?Field;
}