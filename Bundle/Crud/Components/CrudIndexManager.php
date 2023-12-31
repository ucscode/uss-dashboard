<?php

use Ucscode\UssElement\UssElement;
use Ucscode\Form\Form;
use Ucscode\Form\FormField;

class CrudIndexManager extends AbstractCrudIndexConcept
{
    /**
     * This method should be called before "createUI" to avoid unexpected output
     * @method manageBulkActionSubmission
     */
    public function handleBulkActions(CrudBulkActionsInterface $handler): void
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['crud'])) {
            $crud = $_POST['crud'];
            $isValid = Uss::instance()->nonce(self::CRUD_NAME, $crud['__NONCE__'] ?? '');
            if(!$isValid) {
                (new Alert())
                    ->setOption('message', 'Bulk Action - Invalid authorization token')
                    ->type('notification')
                    ->display('warning');
            } else {
                $handler->onSubmit($crud['action'], $crud['data']);
            }
        }
    }

    /**
     * @method createUI
     */
    public function createUI(): UssElement
    {
        $this->createWidgetFrame();
        $this->buildDOMTable();
        $this->tableBlock->appendChild($this->createTableFrame());
        $this->mainBlock->appendChild($this->tableBlock);
        $this->createPaginatorFrame();
        return $this->mainBlock;
    }

    /**
     * @method combineWidgetElements
     */
    protected function createWidgetFrame(): void
    {
        if(!empty($this->widgets) && !$this->hideWidgets) {
            foreach($this->widgets as $widget) {
                $this->widgetBlock->appendChild($widget);
            }
            $this->mainBlock->appendChild($this->widgetBlock);
        }
    }

    /**
     * @method buildDOMTable
     */
    protected function buildDOMTable(): UssElement
    {
        $columns = $this->getTableColumns();

        if(!$this->hideBulkActions) {
            $checkbox = $this->checker(null, function ($input) {
                $input->setAttribute('data-ui-checkbox', 'multiple');
            });
            $columns = ['__checkbox__' => $checkbox] + $columns;
        }

        if($this->hideItemActions === false) {
            // reserve space for item actions
            $columns['__actions__'] = '';
        }

        $this->domTable->setColumns($columns);
        $this->domTable->setDisplayFooter($this->displayTfoot);

        $crudActionParser = new CrudItemIterator(
            $this->modifier,
            $this,
            Closure::fromCallable([$this, 'checker']),
            $this->domTable
        );

        $this->domTable->setData($this->mysqliResult, $crudActionParser);
        return $this->domTable->build();
    }

    /**
     * @method buildTableElements
     */
    protected function createTableFrame(): UssElement
    {
        $tableWrapper = $this->domTable->getTableWrapperElement();
        $tableElement = $this->domTable->getTableElement();
        $tableElement->addAttributeValue('class', 'table-striped');
        $tableElement->setAttribute('data-ui-table', 'crud');

        if($this->isTableWhiteBackground()) {
            $tableWrapper->addAttributeValue('class', 'p-3 bg-white');
        }

        if(!$this->hideBulkActions) {
            $bulkActionFrame = $this->createBulkActionsFrame();
            $this->tableForm->appendChild($bulkActionFrame);
            $this->tableForm->appendChild($tableWrapper);
            $this->tableForm->addField(
                'crud[__NONCE__]',
                (new FormField(Form::NODE_INPUT, Form::TYPE_HIDDEN))
                    ->setWidgetValue(
                        Uss::instance()->nonce(self::CRUD_NAME)
                    )
            );
            return $this->tableForm;
        } else {
            return $tableWrapper;
        }
    }

    /**
     * @method buildBulkActions
     */
    protected function createBulkActionsFrame(): UssElement
    {
        $bulkActionContainer = new UssElement(UssElement::NODE_DIV);
        $bulkActionContainer->setAttribute('class', 'bulk-actions row my-1 py-1');

        $bulkActionColumn = new UssElement(UssElement::NODE_DIV);
        $bulkActionColumn->setAttribute('class', 'col-lg-5 col-md-7 ms-auto d-flex justify-content-end align-items-center');
        $bulkActionContainer->appendChild($bulkActionColumn);

        $bulkSelect = new UssElement(UssElement::NODE_SELECT);
        $bulkSelect->setAttribute('class', 'form-select form-select-sm mb-1 mx-1');
        $bulkSelect->setAttribute('name', 'crud[action]');
        $bulkSelect->setAttribute('required', 'required');
        $bulkSelect->setAttribute('data-ui-bulk-select', self::CRUD_NAME);
        $bulkActionColumn->appendChild($bulkSelect);

        $bulkButton = new UssElement(UssElement::NODE_BUTTON);
        $bulkButton->setAttribute('class', 'btn btn-primary btn-sm text-nowrap');
        $bulkButton->setContent('Apply Action');
        $bulkActionColumn->appendChild($bulkButton);

        foreach($this->bulkActions as $crudAction) {

            $option = new UssElement(UssElement::NODE_OPTION);
            $option->setAttribute('value', $crudAction->getElementAttribute('value'));
            $option->setContent($crudAction->getLabel());

            foreach($crudAction->getElementAttributes() as $key => $value) {
                $option->setAttribute($key, $value);
            }

            $bulkSelect->appendChild($option);
        }

        return $bulkActionContainer;
    }

    /**
     * @method buildPaginatorElement
     */
    protected function createPaginatorFrame(): void
    {
        $this->paginator = new Paginator(
            $this->domTable->gettotalItems(),
            $this->domTable->getItemsPerPage(),
            $this->domTable->getCurrentPage(),
            $this->getUrlPattern()
        );

        if($this->paginator->getNumPages() > 1) {
            $this->paginatorBlock->appendChild(
                $this->paginator->getElement()
            );
            $this->mainBlock->appendChild($this->paginatorBlock);
        }
    }
}
