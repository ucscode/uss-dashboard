<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FieldPedigree;
use Module\Dashboard\Bundle\Crud\Service\Editor\Interface\FormManagerInterface;

abstract class AbstractFormManager implements FormManagerInterface
{
    protected CrudEditorForm $form;
    
    public function __construct(protected string $tableName, array $tableColumns)
    {
        $this->form = new CrudEditorForm($tableName, $tableColumns);
    }

    protected function intersectPedigrees(array $context, FieldPedigree $recentPedigree, FieldPedigree $lastPedigree): void
    {
        $context['attributes'] ??= [];
        if(is_array($context['attributes'])) {
            foreach($context['attributes'] as $name => $value) {
                if(!in_array($name, $this->restrictedAttributes())) {
                    $recentPedigree->widget->setAttribute($name, $value);
                }
            }
        }

        if($recentPedigree->widget->isSelective()) {
            $context['options'] ??= ($lastPedigree->widget->getOptions() ?? []);
            $recentPedigree->widget->setOptions($context['options']);
        }

        if($recentPedigree->widget->isCheckable()) {
            $context['checked'] ??= (bool)($context['checked'] ?? $lastPedigree->widget->isChecked());
            $recentPedigree->widget->setChecked($context['checked']);
        }

        $recentPedigree->widget->setValue($context['value'] ?? $lastPedigree->widget->getValue());

        $recentPedigree->gadget->label->setValue(
            $context['label'] ?? $lastPedigree->gadget->label->getValue()
        );

        foreach(['required', 'disabled', 'readonly'] as $offset) {

            $offsetCase = ucfirst($offset);
            $setter = "set{$offsetCase}";
            $isser = "is{$offsetCase}";

            $recentPedigree->gadget->widget->{$setter}(
                $context[$offset] ?? $lastPedigree->gadget->widget->{$isser}()
            );
        }
    }

    protected function restrictedAttributes(): array
    {
        return [
            'value',
            'checked',
            'required',
            'disabled',
            'readonly',
        ];
    }
}
