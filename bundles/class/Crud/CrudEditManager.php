<?php

use Ucscode\DOMTable\DOMTable;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;
use Ucscode\UssForm\UssFormFieldStack;

class CrudEditManager extends AbstractCrudEditConcept
{
    /**
     * @method createUI
     */
    public function createUI(): UssElement
    {
        /**
         * The Root Container for Edit Manager Components
         */
        $container = new UssElement(UssElement::NODE_DIV);
        $container->setAttribute('class', 'crud-edit-container');

        $position = $this->isAlignActionsLeft() ? 'text-start' : 'text-end';
        $actionClass = sprintf('border-bottom mb-2 %s', $position);

        if(!$this->isReadOnly()) {
            $container = $this->processEditorContent($container, $actionClass);
        } else {
            $container = $this->processReadContent($container, $actionClass);
        }

        $this->insertActions();
        $this->insertWidgets();

        return $container;
    }

    /**
     * @method processReadContent
     */
    public function processReadContent(UssElement $container, string $actionClass): UssElement
    {
        $item = $this->getItem();

        $this->actionContainer = new UssElement(UssElement::NODE_DIV);
        $this->actionContainer->setAttribute('class', 'action-container ' . $actionClass);

        $this->widgetContainer = new UssElement(UssElement::NODE_DIV);
        $this->widgetContainer->setAttribute('class', 'row widget-container');
        $data = [];

        $domTable = new DOMTable($this->tablename);
        $domTable->setColumns([
            'key',
            'value'
        ]);

        foreach($this->fields as $key => $crudField) {
            $data[] = [
                'key' => ucwords(str_replace('_', ' ', $crudField->getWidgetValue())),
                'value' => $item[$key] ?? null
            ];
        }

        $domTable->setData($data);
        $tableElement = $domTable->build();
        $domTable->getTableElement()->addAttributeValue('class', 'table-striped');

        $container->appendChild($this->actionContainer);
        $container->appendChild($this->widgetContainer);
        $container->appendChild($tableElement);

        return $container;
    }

    /**
     * @method createdEditorElement
     */
    protected function processEditorContent(UssElement $container, string $actionClass): UssElement
    {
        /**
         * When user create new item or update an existing item
         * The "CrudEditFormSubmissionHandler" class will handle the request
         */
        new CrudEditFormSubmissionHandler($this);

        /**
         * Create the edit form
         * The form is applicable to both edit and create action
         */
        $this->editForm = new UssForm(
            $this->tablename . '-crud-edit',
            $this->getSubmitUrl(),
            'POST',
            'multipart/form-data'
        );

        /**
         * Give a unique data-* identity to the form
         * This could be helpful for style or javascript usage
         */
        $this->editForm->setAttribute('data-ui-crud-form', self::CRUD_NAME);

        /**
         * Create the action container and append it to the form
         */
        $this->actionContainer = new UssElement(UssElement::NODE_DIV);
        $this->actionContainer->setAttribute('class', 'action-container ' . $actionClass);
        $this->editForm->appendChild($this->actionContainer);

        /**
         * Create the widget container and append it to the form
         */
        $this->widgetContainer = (new UssElement(UssElement::NODE_DIV));
        $this->widgetContainer->setAttribute('class', 'widget-container');
        $this->editForm->appendChild($this->widgetContainer);

        /**
         * The create the default fieldstack (fieldset), which holds the UssFormFields
         */
        $this->editForm->addFieldStack('default');

        foreach($this->fields as $key => $crudField) {
            $this->editForm->addField($key, $crudField);
        }

        $this->populateForm();

        $nonceField = new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_HIDDEN);
        $nonceField->setWidgetValue(Uss::instance()->nonce($this->tablename));
        
        $this->editForm->addField('__NONCE__', $nonceField);

        $container->appendChild($this->editForm);

        return $container;
    }

    /**
     * @method populateForm
     */
    protected function populateForm(): void
    {
        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        if($isPost || $this->getItem()) {
            $keys = ['__ACTION__', '__NONCE__'];
            $dataToPopulate = $isPost ? $_POST : $this->getItem();
            foreach($keys as $key) {
                if(array_key_exists($key, $dataToPopulate)) {
                    unset($dataToPopulate[$key]);
                }
            }
            $this->editForm->populate($dataToPopulate);
        }
    }
}
