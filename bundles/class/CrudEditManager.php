<?php

use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;

class CrudEditManager extends AbstractCrudEditManager
{
    public function __construct(string $tablename)
    {
        parent::__construct($tablename);
        $this->createDefaultFields();
    }

    /**
     * @method createUI
     */
    public function createUI(): UssElement
    {
        $container = new UssElement(UssElement::NODE_DIV);
        $container->setAttribute('class', 'crud-edit-container');

        $form = new UssForm(
            $this->tablename . '-crud-edit',
            $this->getSubmitUrl(),
            'POST',
            'multipart/form-data'
        );

        foreach($this->fields as $key => $crudField) {
            if($crudField->getNodeType() !== CrudField::TYPE_EDITOR) {
                $form->add(
                    $key,
                    $this->getNodeName($crudField),
                    $this->getFieldContext($crudField),
                    $this->getFieldConfig($crudField)
                );
                if($crudField->hasLineBreak()) {
                    $form->addRow();
                }
            } else {
                $this->createCustomField($form);
            }
        }

        $container->appendChild($form);

        return $container;
    }

    /**
     * @method createDefaultFields
     */
    protected function createDefaultFields(): void
    {
        $sQuery = (new SQuery())
            ->select('COLUMN_NAME, DATA_TYPE')
            ->from('information_schema.COLUMNS')
            ->where('TABLE_NAME', $this->tablename);

        $result = Uss::instance()->mysqli->query($sQuery->getQuery());

        if ($result->num_rows) {
            while ($row = $result->fetch_assoc()) {
                $this->createSingleField($row['COLUMN_NAME'], $row['DATA_TYPE']);
            }
        }
    }

    /**
     * @method createSingleField
     */
    protected function createSingleField(string $name, string $type): void
    {
        $crudField = (new CrudField())
            ->setLabel(str_replace('_', ' ', $name))
            ->setNodeType($this->getDefaultNodeType(strtolower($name), strtoupper($type)))
            ->setLineBreak(true);

        $this->setField($name, $crudField);
    }

    /**
     * @method getDefaultFieldType
     */
    protected function getDefaultNodeType(string $name, string $type): string
    {
        if(in_array($type, [...self::DATASET['integer'], ...self::DATASET['float']])) {
            $nodeType = CrudField::TYPE_NUMBER;
        } elseif(in_array($type, self::DATASET['date'])) {
            $nodeType = CrudField::TYPE_DATE;
        } elseif(in_array($type, self::DATASET['text'])) {
            $nodeType = CrudField::TYPE_TEXTAREA;
        } else {
            $nodeType = CrudField::TYPE_INPUT;
        }
        return $nodeType;
    }

    /**
     * @method getNodename
     */
    protected function getNodename(CrudField $crudField): string
    {
        switch($crudField->getNodeType()) {
            case CrudField::TYPE_SELECT:
                return UssForm::NODE_SELECT;
            case CrudField::TYPE_TEXTAREA;
                return UssForm::NODE_TEXTAREA;
            default:
                return UssForm::NODE_INPUT;
        };
    }

    /**
     * @method getFieldContext
     */
    protected function getFieldContext(CrudField $crudField): array|string|null
    {
        $context = null;
        $nodeType = $crudField->getNodeType();

        switch($nodeType) {
            case CrudField::TYPE_SELECT:
                $context = $crudField->getSelectOptions();
                break;
            case CrudField::TYPE_BOOLEAN:
                $context = UssForm::TYPE_SWITCH;
                break; 
            case CrudField::TYPE_INPUT:
                $context = UssForm::TYPE_TEXT;
                break;
            case CrudField::TYPE_DATE:
                $context = UssForm::TYPE_DATETIME_LOCAL;
                break;
            default:
                $context = $nodeType;
        }

        return $context;
    }

    /**
     * @method getFieldConfig
     */
    protected function getFieldConfig(CrudField $crudField): array
    {
        $config = [
            'label' => $crudField->getLabel(),
            'value' => $crudField->getValue(),
            'required' => $crudField->isRequired(),
            'column' => $crudField->getColumnClass(),
            'class' => $crudField->getClass(),
            'attr' => [],
        ];

        if(empty($config['label'])) {
            $config['label_class'] = 'd-none';
        }

        $icon = $crudField->getIcon();
        if($icon) {
            $position = $crudField->isIconPositionRight() ? 'append' : 'prepend';
            $config['group'] = [
                $position => "<i class='{$icon}'></i>"
            ];
        };

        $error = $crudField->getError();
        if($error) {
            $config['report'] = [
                'message' => '<i class="bi bi-exclamation-circle me-1"></i>' . $error,
                'class' => 'text-danger fs-12px'
            ];
        }

        $attributes = $crudField->getAttributes();

        foreach($attributes as $key => $attribute) {
            $config['attr'][$key] = $attribute;
        }

        if($crudField->isReadonly()) {
            $config['attr']['readonly'] = 'readonly';
        }

        if($crudField->isDisabled()) {
            $config['attr']['disabled'] = 'disabled';
        }

        return $config;
    }

    /**
     * @method createCustomField
     */
    public function createCustomField(UssForm $form): void
    {

    }
}
