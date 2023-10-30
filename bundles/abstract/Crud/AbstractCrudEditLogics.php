<?php

use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;

abstract class AbstractCrudEditLogics extends AbstractCrudEditManager
{
    protected UssElement $actionContainer;
    protected UssElement $widgetContainer;
    protected UssForm $editForm;
    protected User $currentUser;
    protected string $nonceKey;
    protected string $baseUrl;

    public function __construct(string $tablename)
    {
        parent::__construct($tablename);
        $this->getCurrentUser();
        $this->createDefaultFields();
        $this->createDefaultActions();
        $this->createDefaultWidgets();
        $this->baseUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * @method getCurrentUser
     */
    protected function getCurrentUser(): void
    {
        $this->currentUser = new User();
        $this->currentUser->getFromSession();
        $this->nonceKey = sprintf(
            '%s-%s',
            $this->tablename,
            $this->currentUser->getId()
        );
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
            ->setType($this->getDefaultNodeType(strtolower($name), strtoupper($type)))
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
    protected function createCustomField(UssForm $form): void
    {

    }

    /**
     * @method createDefaultActions
     */
    protected function createDefaultActions(): void
    {
        $actionSubmit = (new CrudAction())
            ->setElementAttribute('class', 'btn btn-primary btn-sm m-2')
            ->setElementAttribute('name', '__ACTION__')
            ->setElementAttribute('type', 'submit');

        if(!empty($this->getItem())) {
            $actionName = self::ACTION_UPDATE;
            $actionSubmit
                ->setElementAttribute('value', self::ACTION_UPDATE)
                ->setLabel('Save Changes')
                ->setIcon('bi bi-floppy');
        } else {
            $actionName = self::ACTION_CREATE;
            $actionSubmit
                ->setElementAttribute('value', self::ACTION_CREATE)
                ->setLabel('Add New')
                ->setIcon('bi bi-plus-circle');
        }

        $this->setAction($actionName, $actionSubmit);
    }

    /**
     * @method createDefaultWidgets
     */
    protected function createDefaultWidgets(): void
    {
        // no default widgets to create
    }

    /**
     * @method insertActions
     */
    protected function insertActions(): void
    {
        foreach($this->actions as $key => $crudAction) {
            $nodeName = $crudAction->getElementType() === CrudAction::TYPE_BUTTON ?
                UssElement::NODE_BUTTON : UssElement::NODE_A;
            $actionElement = new UssElement($nodeName);
            $icon = $crudAction->getIcon();
            if(!empty($icon)) {
                $icon = sprintf("<i class='%s'></i>", $icon);
            }
            $actionElement->setContent(sprintf("%s %s", $icon, $crudAction->getLabel()));
            foreach($crudAction->getElementAttributes() as $name => $value) {
                $actionElement->setAttribute($name, $value);
            };
            $this->actionContainer->appendChild($actionElement);
        }
    }

    /**
     * @method addFormNonce
     */
    protected function addFormNonce(): void
    {
        $uss = Uss::instance();

        $nonce = $uss->nonce($this->nonceKey);

        $this->editForm->add(
            '__NONCE__',
            UssForm::NODE_INPUT,
            UssForm::TYPE_HIDDEN,
            [
                'value' => $nonce
            ]
        );
    }

    /**
     * @method insertWidgets
     */
    protected function insertWidgets(): void
    {

    }
}
