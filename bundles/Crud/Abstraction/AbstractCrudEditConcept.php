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

        $this->editForm = new UssForm(
            $this->tablename . '-crud-edit',
            $_SERVER['REQUEST_URI'],
            'POST',
            'multipart/form-data'
        );
        
        $this->editForm->setAttribute('data-ui-crud-form', self::CRUD_NAME);
        
        $this->createDefaultFields();
        (new CrudItemActionBuilder($this));
        $this->createDefaultWidgets(); // void
    }

    public function getEditForm(): UssForm
    {
        return $this->editForm;
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
                $crudField = $this->createEditField(
                    strtoupper($row['DATA_TYPE']),
                    strtolower($columnName)
                );
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
    protected function createEditField(string $type, string $columnName): UssFormField
    {
        $nodeName = UssForm::NODE_INPUT;
        $nodeType = UssForm::TYPE_TEXT;
        $attributes = [
            'row' => [],
            'widget' => []
        ];

        $isInteger = in_array($type, self::DATASET['INTEGER']);
        $isFloat = in_array($type, self::DATASET['FLOAT']);
        $isDate = in_array($type, self::DATASET['DATE']);
        $isText = in_array($type, self::DATASET['TEXT']);
        $isChar = in_array($type, self::DATASET['CHARACTER']);
        
        if($isInteger || $isFloat) {
            $nodeType = UssForm::TYPE_NUMBER;
            $attributes['row']['class'] = ' col-md-7';
            if($isFloat) {
                $attributes['widget']['step'] = '0.01';
            }
        } elseif($isText) {
            $nodeName = UssForm::NODE_TEXTAREA;
            $nodeType = null;
            $attributes['row']['class'] = ' col-md-9';
        } elseif($isDate) {
            $nodeType = UssForm::TYPE_DATETIME_LOCAL;
            $attributes['row']['class'] = ' col-md-7';
        } else {
            $attributes['row']['class'] = ' col-md-9';
        }

        $field = new UssFormField($nodeName, $nodeType);

        foreach($attributes as $el => $nodeAttr) {
            foreach($nodeAttr as $attr => $value) {
                $method = 'set' . ucfirst($el) . 'Attribute';
                $field->{$method}($attr, $value, true);
            }
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
