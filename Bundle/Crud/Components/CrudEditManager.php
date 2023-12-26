<?php

use Ucscode\DOMTable\DOMTable;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

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
        $itemCast = [];

        $this->actionContainer = new UssElement(UssElement::NODE_DIV);
        $this->actionContainer->setAttribute('class', 'action-container ' . $actionClass);

        $this->widgetContainer = new UssElement(UssElement::NODE_DIV);
        $this->widgetContainer->setAttribute('class', 'row widget-container');

        $domTable = new DOMTable($this->tablename);
        $domTable->setColumns([
            'key',
            'value'
        ]);

        $preservedFields = $this->getEditForm()->getFields();

        foreach($preservedFields as $key => $crudField) {
            $itemCast[] = [
                'key' => $this->refactorKey($key),
                'value' => $item[$key] ?? null
            ];
        }

        $domTable->setData($itemCast, $this->domtableInterface);
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
         * Create the action container and append it to the FORM Element
         */
        $this->actionContainer = new UssElement(UssElement::NODE_DIV);
        $this->actionContainer->setAttribute('class', 'action-container ' . $actionClass);
        $this->editForm->prependChild($this->actionContainer);

        /**
         * Create the widget container and append it to the FORM Element
         */
        $this->widgetContainer = (new UssElement(UssElement::NODE_DIV));
        $this->widgetContainer->setAttribute('class', 'widget-container');
        $this->editForm->insertAfter(
            $this->widgetContainer,
            $this->actionContainer
        );

        $preservedFields = $this->getEditForm()->getFields();

        foreach($preservedFields as $key => $crudField) {
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
        $item = $this->getItem();
        if($isPost || $item) {
            $keys = ['__ACTION__', '__NONCE__'];
            $dataToPopulate = $isPost ? $_POST : $item;
            foreach($keys as $key) {
                if(array_key_exists($key, $dataToPopulate)) {
                    unset($dataToPopulate[$key]);
                }
            }
            $this->editForm->populate($dataToPopulate);
        }
    }

    /**
     * @method refactorKey
     */
    protected function refactorKey(string $key): string
    {
        $pattern = ['/_/i', '/[\[\]]/i'];
        $replacement = [' ', ''];
        $key = preg_replace($pattern, $replacement, $key);
        return ucwords($key);
    }
}
