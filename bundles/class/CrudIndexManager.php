<?php

use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\UssForm\UssForm;

class CrudIndexManager extends AbstractCrudIndexManager
{
    public function __construct(string $tablename)
    {
        parent::__construct($tablename);
        $this->configureProperties();
        $this->setDefaultBulkActions();
        $this->setDefaultItemActions();
    }

    public function createUI(?DOMTableInterface $fabricator = null): string
    {
        $this->mainContainer->appendChild($this->widgetContainer);
        $this->tableContainer->appendChild($this->tableForm);
        $this->mainContainer->appendChild($this->tableContainer);
        $this->mainContainer->appendChild($this->paginatorContainer);

        $this->buildBulkActionsElement();
        $this->buildDOMTable($fabricator);

        return $this->mainContainer->getHTML(true);
    }

    /**
     * @method configureProperties
     */
    protected function configureProperties(): void
    {
        $uss = Uss::instance();

        $tableColumns = array_map(function ($value) {
            return str_replace('_', ' ', $value);
        }, $uss->getTableColumns($this->tablename));

        $this->setMultipleTableColumns($tableColumns);

        $divNodes = [
            'mainContainer' => 'container',
            'widgetContainer' => 'widget',
            'paginatorContainer' => 'paginator',
            'tableContainer' => 'table'
        ];

        foreach($divNodes as $key => $classname) {
            $this->{$key} = new UssElement(UssElement::NODE_DIV);
            $this->{$key}->setAttribute('class', 'crud-' . $classname);
        };

        $this->tableForm = new UssForm(
            $this->tablename . '-crud-form',
            $_SERVER['REQUEST_URI'],
            'POST'
        );

        $this->domTable = new DOMTable($this->tablename);
        $this->domTable->getTableElement()->addAttributeValue('class', 'table-striped');

        $this->sQuery = (new SQuery())->select('*', $this->tablename);
        $this->evalSQueryUpdate();
    }

    /**
     * @method configureBulkActions
     */
    public function setDefaultBulkActions(): void
    {
        $noAction = (new CrudAction())
            ->setLabel('- Select Action -')
            ->setElementAttribute('value', '');

        $this->addBulkAction('no-action', $noAction);

        $deleteAction = (new CrudAction())
            ->setLabel('Delete')
            ->setElementAttribute('value', 'delete');

        $this->addBulkAction('delete', $deleteAction);
    }

    /**
     * @method configureItemActions
     */
    public function setDefaultItemActions(): void
    {
        $this->addItemAction('edit', new class () implements CrudActionInterface {
            public function forEachItem(array $data): CrudAction
            {
                $curdAction = new CrudAction();

                $curdAction
                    ->setLabel('Edit')
                    ->setIcon('bi bi-person')
                    ->setElementType(CrudAction::TYPE_BUTTON)
                    ->setElementAttribute('type', 'button');

                return $curdAction;
            }
        });

        $this->addItemAction('delete', new class ($this) implements CrudActionInterface {
            public function __construct(
                private CrudIndexManager $crudIndexManager
            ){}

            public function forEachItem(array $data): CrudAction
            {
                $crudAction = (new CrudAction())
                    ->setLabel('Delete')
                    ->setIcon('bi bi-trash')
                    ->setElementType(CrudAction::TYPE_ANCHOR)
                    ->setElementAttribute('href', 'https://example.com')
                    ->setElementAttribute('target', '_blank');
                
                if($this->crudIndexManager->isDisplayItemActionsAsButton()) {
                    $crudAction->setElementAttribute('class', 'btn btn-outline-danger btn-sm text-nowrap');
                }

                return $crudAction;
            }
        });
    }

    /**
     * @method buildDOMTable
     */
    protected function buildDOMTable(?DOMTableInterface $fabricator): void
    {
        $this->domTable->setData($this->mysqliResult);

        $columns = $this->getTableColumns();

        if(!$this->hideBulkActions) {
            $columns = ['checkbox' => $this->checker()] + $columns;
        }

        if(!$this->hideItemActions) {
            $columns['actions'] = '';
        }

        $this->domTable->setMultipleColumns($columns);
        $this->domTable->setCurrentPage($this->paginator->getCurrentPage());
        $this->domTable->setDisplayFooter($this->displayTfoot);
        $this->domTable->setRowsPerPage($this->paginator->getItemsPerPage());

        $crudActionParser = new CrudItemIterator(
            $fabricator,
            $this,
            Closure::fromCallable([$this, 'checker']),
            $this->domTable
        );

        $this->domTable->build($crudActionParser);
    }

    /**
     * @method checker
     */
    protected function checker(?string $value = null): UssElement
    {
        $container = new UssElement(UssElement::NODE_DIV);
        $container->setAttribute('class', 'form-check');
        $checker = new UssElement(UssElement::NODE_INPUT);
        $checker->setAttribute('type', 'checkbox');
        $checker->setAttribute('class', 'form-check-input');
        if(!is_null($value)) {
            $checker->setAttribute('name', 'crud[data][]');
            $checker->setAttribute('value', $value);
        }
        $container->appendChild($checker);
        return $container;
    }

    /**
     * @method buildBulkActions
     */
    protected function buildBulkActionsElement(): void
    {
        if(!$this->hideBulkActions) {

            $bulkActionContainer = new UssElement(UssElement::NODE_DIV);
            $bulkActionContainer->setAttribute('class', 'bulk-actions row my-1 py-1 border-bottom');

            $bulkActionColumn = new UssElement(UssElement::NODE_DIV);
            $bulkActionColumn->setAttribute('class', 'col-lg-5 col-md-7 ms-auto d-flex justify-content-end align-items-center');
            $bulkActionContainer->appendChild($bulkActionColumn);

            $bulkSelect = new UssElement(UssElement::NODE_SELECT);
            $bulkSelect->setAttribute('class', 'form-select form-select-sm mb-1 mx-1');
            $bulkSelect->setAttribute('name', 'crud[action]');
            $bulkSelect->setAttribute('required', 'required');
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

            $this->tableForm->appendChild(
                $bulkActionContainer
            );

            $this->tableForm->appendChild(
                $this->domTable->getTableContainerElement()
            );
        }
    }
}
