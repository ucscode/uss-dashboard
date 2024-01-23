<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\Crud\Service\Editor\Interface\CrudEditorFormInterface;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

abstract class AbstractCrudEditorForm extends AbstractDashboardForm implements CrudEditorFormInterface
{
    public const PERSISTENCE_INSERT_ID = 'entity.persist.insert_id';
    public const PERSISTENCE_ENABLED = 'entity.persist.enabled';
    public const PERSISTENCE_STATUS = 'entity.persist.status';
    public const PERSISTENCE_ERROR = 'entity.persist.error';
    public const PERSISTENCE_TYPE = 'entity.persist.type';

    protected string $nonceContext;
    protected Flash $flash;

    public function __construct(protected CrudEditor $crudEditor)
    {
        parent::__construct();
        $this->nonceContext = UssImmutable::SECRET_KEY . $this->crudEditor->tableName;
        $this->generateFormFields();
        $this->flash = Flash::instance();

        $this->setProperty(self::PERSISTENCE_ENABLED, true);
        $this->setProperty(self::PERSISTENCE_STATUS, false);
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