<?php

use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

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
                $columnName = $row['COLUMN_NAME'];
                $cellLabel = str_replace('_', ' ', $columnName);
                $crudField = $this->createEditField($row['DATA_TYPE']);
                $crudField->setLabelValue($cellLabel);
                $this->setField($columnName, $crudField);
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
    protected function createEditField(string $type): UssFormField
    {
        $nodeName = UssForm::NODE_INPUT;
        $nodeType = UssForm::TYPE_TEXT;
        $nodeAttr = [];

        $isInteger = in_array($type, self::DATASET['INTEGER']);
        $isFloat = in_array($type, self::DATASET['FLOAT']);
        $isDate = in_array($type, self::DATASET['DATE']);
        $isChar = in_array($type, self::DATASET['CHARACTER']);
        $isText = in_array($type, self::DATASET['TEXT']);

        if($isInteger || $isFloat) {
            $nodeType = UssForm::TYPE_NUMBER;
            if($isFloat) {
                $nodeAttr['step'] = '0.01';
            }
        } elseif($isText) {
            $nodeName = UssForm::NODE_TEXTAREA;
            $nodeType = null;
        } elseif($isDate) {
            $nodeType = UssForm::TYPE_DATETIME_LOCAL;
        };

        $field = new UssFormField($nodeName, $nodeType);

        foreach($nodeAttr as $key => $value) {
            $field->setWidgetAttribute($key, $value);
        };

        return $field;
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
