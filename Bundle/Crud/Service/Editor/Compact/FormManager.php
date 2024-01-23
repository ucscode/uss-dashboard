<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Compact;

use Module\Dashboard\Bundle\Crud\Service\Editor\Abstract\AbstractFormManager;
use Ucscode\UssForm\Field\Field;

class FormManager extends AbstractFormManager
{
    public function getForm(): CrudEditorForm
    {
        return $this->form;
    }

    public function configureField(string $name, array $context): ?Field
    {
        $lastPedigree = $this->form->getFieldPedigree($name);
        if($lastPedigree) {
            $field = new Field(
                $context['nodeName'] ?? Field::NODE_INPUT,
                $context['nodeType'] ?? Field::TYPE_TEXT
            );
            $lastPedigree->collection->addField($name, $field);
            $recentPedigree = $this->form->getFieldPedigree($name);
            $this->intersectPedigrees($context, $recentPedigree, $lastPedigree);
            return $recentPedigree->field;
        }
        return null;
    }
}