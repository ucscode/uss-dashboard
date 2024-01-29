<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Interface;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Resource\Service\Pedigree\FieldPedigree;

interface FormManagerInterface
{
    public function getForm(): CrudEditorForm;
    public function configureField(string $name, array $context): ?FieldPedigree;
}