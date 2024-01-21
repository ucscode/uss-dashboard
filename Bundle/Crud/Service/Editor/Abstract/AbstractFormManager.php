<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FieldPedigree;
use Module\Dashboard\Bundle\Crud\Service\Editor\Interface\FormManagerInterface;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;
use Uss\Component\Kernel\Uss;

abstract class AbstractFormManager implements FormManagerInterface
{
    public const SUBMIT_KEY = ':submit';
    public const NONCE_KEY = '__nonce';

    protected Form $form;
    protected Collection $collection;
    
    public function __construct(protected string $tablename, array $tableColumns)
    {
        $this->form = new Form();
        $this->collection = $this->form->getCollection(Form::DEFAULT_COLLECTION);
        $this->generateFormFields($tableColumns);
    }

    protected function generateFormFields(array $tableColumns): void
    {
        foreach($tableColumns as $name => $label) {
            $field = new Field();
            $field->getElementContext()->label->setValue($label);
            $this->collection->addField($name, $field);
        }

        $nonceValue = Uss::instance()->nonce($this->tablename);
        $nonceField = new Field(Field::NODE_INPUT, Field::TYPE_HIDDEN);
        $nonceField->getElementContext()->widget
            ->setAttribute("name", self::NONCE_KEY)
            ->setValue($nonceValue);

        $this->collection->addField(self::NONCE_KEY, $nonceField);

        $submitButton = new Field(Field::NODE_BUTTON, Field::TYPE_SUBMIT);
        $submitButton->getElementContext()->widget
            ->setButtonContent("Create New")
            ->setRequired(false)
            ->setFixed(true)
        ;
        $this->collection->addField(self::SUBMIT_KEY, $submitButton);
    }

    protected function intersectPedigrees(array $context, FieldPedigree $recentPedigree, FieldPedigree $lastPedigree): void
    {
        if(!empty($context['attributes']) && is_array($context['attributes'])) {
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
