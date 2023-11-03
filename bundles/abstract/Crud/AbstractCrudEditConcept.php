<?php

use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;

abstract class AbstractCrudEditConcept extends AbstractCrudEditManager
{
    protected UssElement $actionContainer;
    protected UssElement $widgetContainer;
    protected UssForm $editForm;

    public function __construct(string $tablename)
    {
        parent::__construct($tablename);
        $this->createDefaultFields();
        new CrudItemActionBuilder($this);
        $this->createDefaultWidgets(); // void
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
                $name = $row['COLUMN_NAME'];
                $type = $row['DATA_TYPE'];

                $crudField = (new CrudField())
                    ->setLabel(str_replace('_', ' ', $name))
                    ->setType($this->getFieldType(strtoupper($type)))
                    ->setLineBreak(true);

                $this->setField($name, $crudField);
            }
        }
    }

    /**
     * @method createDefaultWidgets
     */
    protected function createDefaultWidgets(): void
    {
        // no default widgets to create
    }

    /**
     * @method getDefaultFieldType
     */
    protected function getFieldType(string $type): string
    {
        $nodeTypes = [
            CrudField::TYPE_NUMBER => array(...self::DATASET['integer'], ...self::DATASET['float']),
            CrudField::TYPE_DATE => self::DATASET['date'],
            CrudField::TYPE_TEXTAREA => self::DATASET['text'],
            CrudField::TYPE_INPUT => null
        ];

        foreach($nodeTypes as $key => $value) {
            if(is_null($value) || in_array($type, $value, true)) {
                return $key;
            }
        }
    }

    /**
     * @method getNodename
     */
    protected function getNodename(CrudField $crudField): string
    {
        switch($crudField->getType()) {
            case CrudField::TYPE_SELECT:
                return UssForm::NODE_SELECT;
            case CrudField::TYPE_TEXTAREA:
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
        $nodeType = $crudField->getType();

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
        $form_label = in_array(
            $crudField->getType(),
            [
                CrudField::TYPE_CHECKBOX,
                CrudField::TYPE_RADIO,
            ]
        ) ? 'form-check-label' : 'form-label';

        $config = [
            'label' => $crudField->getLabel(),
            'value' => $crudField->getValue(),
            'required' => $crudField->isRequired(),
            'label_class' => ($crudField->isRequired() ? '--required ' : null) . $form_label,
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

        $attributes = $crudField->getElementAttributes();

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
    protected function createCustomField(UssForm $form): void
    {

    }

    /**
     * @method insertActions
     */
    protected function insertActions(): void
    {
        $inserted = 0;

        foreach($this->actions as $key => $crudAction) {

            if($this->isWorthyAction($key)) {

                $isButton = $crudAction->getElementType() === CrudAction::TYPE_BUTTON;
                $nodeName = $isButton ? UssElement::NODE_BUTTON : UssElement::NODE_A;
                $actionElement = new UssElement($nodeName);

                $icon = $crudAction->getIcon();
                if(!empty($icon)) {
                    $icon = sprintf("<i class='%s me-1'></i>", $icon);
                }

                $actionElement->setContent(sprintf("%s %s", $icon, $crudAction->getLabel()));

                foreach($crudAction->getElementAttributes() as $name => $value) {
                    $actionElement->setAttribute($name, $value);
                };

                $this->actionContainer->appendChild($actionElement);

                $inserted++;
            }

        }

        if(!$inserted) {
            $this->actionContainer->getParentElement()
            ->removeChild($this->actionContainer);
        }
    }

    /**
     * @method isWorthAction
     */
    public function isWorthyAction(string $action): bool
    {
        $worthy = false;
        switch($this->currentAction) {
            case self::ACTION_CREATE:
                $worthy = in_array($action, [self::ACTION_CREATE, self::ACTION_INDEX]);
                break;
            case self::ACTION_UPDATE:
                $worthy = in_array($action, [self::ACTION_UPDATE, self::ACTION_INDEX]);
                break;
            case self::ACTION_READ:
                $worthy = in_array($action, [self::ACTION_DELETE, self::ACTION_INDEX]);
                break;
        }
        return $worthy;
    }

    /**
     * @method insertWidgets
     */
    protected function insertWidgets(): void
    {

    }
}
