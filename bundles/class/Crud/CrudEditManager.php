<?php

use Ucscode\DOMTable\DOMTable;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;

class CrudEditManager extends AbstractCrudEditConcept
{
    /**
     * @method createUI
     */
    public function createUI(): UssElement
    {
        $container = new UssElement(UssElement::NODE_DIV);
        $container->setAttribute('class', 'crud-edit-container');

        $position = $this->getAlignActionsLeft() ? 'text-start' : 'text-end';
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
     * @method createdEditorElement
     */
    protected function processEditorContent(UssElement $container, string $actionClass): UssElement
    {
        new CrudEditFormSubmissionHandler($this);

        $this->editForm = new UssForm(
            $this->tablename . '-crud-edit',
            $this->getSubmitUrl(),
            'POST',
            'multipart/form-data'
        );

        $this->editForm->setAttribute('data-ui-crud-form', self::CRUD_NAME);

        $this->actionContainer = $this->editForm->addRow('action-container');
        $this->actionContainer
            ->removeAttributeValue('class', 'row')
            ->addAttributeValue('class', $actionClass);

        $this->widgetContainer = $this->editForm->addRow('widget-container');

        $this->editForm->addRow();

        foreach($this->fields as $key => $crudField) {
            if($crudField->getType() !== CrudField::TYPE_EDITOR) {
                $this->editForm->add(
                    $key,
                    $this->getNodeName($crudField),
                    $this->getFieldContext($crudField),
                    $this->getFieldConfig($crudField)
                );
                if($crudField->hasLineBreak()) {
                    $this->editForm->addRow();
                }
            } else {
                $this->createCustomField($this->editForm);
            }
        }

        $this->populateForm();

        $this->editForm->add(
            '__NONCE__',
            UssForm::NODE_INPUT,
            UssForm::TYPE_HIDDEN,
            [
                'value' => Uss::instance()->nonce($this->tablename)
            ]
        );

        $container->appendChild($this->editForm);

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
                'key' => ucwords(str_replace('_', ' ', $crudField->getLabel())),
                'value' => $item[$key]
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
     * @method populateForm
     */
    protected function populateForm(): void
    {
        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        $populate = $isPost || $this->getItem();
        $discardKey = ['__ACTION__', '__NONCE__'];
        $dataToPopulate = $_POST;

        foreach($discardKey as $key) {
            if(isset($dataToPopulate[$key])) {
                unset($dataToPopulate[$key]);
            }
        }

        if($populate) {
            if($isPost) {
                $this->editForm->populate($dataToPopulate);
            } else {
                $this->editForm->populate($this->getItem());
            }
            $this->editForm->populate(true);
        }
    }
}
