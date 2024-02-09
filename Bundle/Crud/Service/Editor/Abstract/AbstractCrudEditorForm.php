<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\Flash\Flash;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Kernel\Uss;

abstract class AbstractCrudEditorForm extends AbstractCrudEditorFormFoundation
{
    public function __construct(protected CrudEditor $crudEditor)
    {
        parent::__construct();
        $this->nonceContext = $_ENV['APP_SECRET'] . $this->crudEditor->tableName;
        $this->generateFormFields();
        $this->flash = Flash::instance();
    }

    protected function generateFormFields(): void
    {
        foreach($this->crudEditor->tableColumnsLabelled as $name => $label) {
            $field = new Field();
            $field->getElementContext()->label->setValue($label);
            $this->collection->addField($name, $field);
        }

        $nonceValue = Uss::instance()->nonce($this->nonceContext);
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
}