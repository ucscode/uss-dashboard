<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor;

use Module\Dashboard\Bundle\Crud\Component\CrudWidgetManager;
use Module\Dashboard\Bundle\Crud\Service\Editor\Abstract\AbstractCrudEditor_Level3;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Resource\Service\Pedigree\FieldPedigree;

class CrudEditor extends AbstractCrudEditor_Level3
{
    public function build(): UssElement
    {
        parent::build();
        new CrudWidgetManager($this);
        $this->getForm()->export();
        return $this->baseContainer;
    }

    public function configureField(string $name, array $array): ?FieldPedigree
    {
        return $this->formManager->configureField($name, $array);
    }
}