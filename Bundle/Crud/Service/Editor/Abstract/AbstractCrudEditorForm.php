<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Service\Editor\Interface\CrudEditorFormInterface;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Kernel\Uss;

abstract class AbstractCrudEditorForm extends AbstractDashboardForm implements CrudEditorFormInterface
{
    public function __construct(protected string $tableName, array $tableColumns)
    {
        parent::__construct();
        $this->generateFormFields($tableColumns);
    }

    protected function generateFormFields(array $tableColumns): void
    {
        foreach($tableColumns as $name => $label) {
            $field = new Field();
            $field->getElementContext()->label->setValue($label);
            $this->collection->addField($name, $field);
        }

        $nonceValue = Uss::instance()->nonce($this->tableName);
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